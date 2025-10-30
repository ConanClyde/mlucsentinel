<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Events\PaymentUpdated;
use App\Events\UserUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\StaticDataCacheService;
use App\Services\StickerGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    protected $stickerGenerator;

    public function __construct(StickerGenerator $stickerGenerator)
    {
        $this->stickerGenerator = $stickerGenerator;
    }

    /**
     * Show the student registration page.
     */
    public function index()
    {
        $colleges = StaticDataCacheService::getColleges();
        $vehicleTypes = StaticDataCacheService::getVehicleTypes();

        return view('admin.registration.student', [
            'pageTitle' => 'Student Registration',
            'colleges' => $colleges,
            'vehicleTypes' => $vehicleTypes,
        ]);
    }

    /**
     * Store a newly created student.
     */
    public function store(StoreStudentRequest $request)
    {
        try {
            $validated = $request->validated();

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make('temp_password_'.time()), // Temporary password since students don't login
                'user_type' => 'student',
                'is_active' => true,
            ]);

            // Handle license image upload (store without optimization to avoid GD dependency)
            $licenseImagePath = null;
            if ($request->hasFile('license_image')) {
                $file = $request->file('license_image');
                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $licenseImagePath = $file->storeAs('licenses', $filename, 'public');
            }

            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'college_id' => $request->college_id,
                'student_id' => $request->student_id,
                'license_no' => $request->license_no,
                'license_image' => $licenseImagePath,
                'expiration_date' => now()->addYears(4), // 4 years from now
            ]);

            // Create vehicles and generate stickers
            $vehicleIds = [];
            foreach ($request->vehicles as $vehicleData) {
                $plateNumber = $vehicleData['plate_no'] ?? null;

                // For Electric Vehicle (type_id 3), plate_no must be null
                if ($vehicleData['type_id'] == 3) {
                    $plateNumber = null;
                } else {
                    // For other vehicles, ensure plate_no is not empty string
                    $plateNumber = ! empty($plateNumber) ? $plateNumber : null;
                }

                $color = $this->stickerGenerator->determineStickerColor('student', null, $plateNumber);
                $stickerNumber = $this->stickerGenerator->generateNextStickerNumber($color);

                $vehicle = Vehicle::create([
                    'user_id' => $user->id,
                    'type_id' => $vehicleData['type_id'],
                    'plate_no' => $plateNumber,
                    'color' => $color,
                    'number' => $stickerNumber,
                    'is_active' => true,
                ]);

                // Generate sticker
                try {
                    $stickerPath = $this->stickerGenerator->generateVehicleSticker(
                        $stickerNumber,
                        $vehicle->type->name ?? 'Vehicle',
                        $plateNumber,
                        $color,
                        $vehicle->id
                    );

                    $vehicle->update(['sticker' => $stickerPath]);
                } catch (\Exception $e) {
                    \Log::error("Failed to generate sticker for vehicle {$vehicle->id}: ".$e->getMessage());
                    // Continue without sticker - it can be regenerated later
                }

                // Broadcast vehicle creation
                $vehicle->load(['user', 'type']);
                broadcast(new VehicleUpdated($vehicle, 'created', auth()->user()->first_name.' '.auth()->user()->last_name));

                $vehicleIds[] = $vehicle->id;
            }

            // Create batched payment for all vehicles
            if (count($vehicleIds) > 0) {
                $batchId = count($vehicleIds) > 1 ? 'BATCH-'.strtoupper(uniqid()) : null;
                $totalAmount = count($vehicleIds) * 15.00;

                // Create main payment record
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'vehicle_id' => $vehicleIds[0],
                    'type' => 'sticker_fee',
                    'status' => 'pending',
                    'amount' => $totalAmount,
                    'reference' => 'STK-'.strtoupper(uniqid()),
                    'batch_id' => $batchId,
                    'vehicle_count' => count($vehicleIds),
                ]);

                // Create child payment records for other vehicles
                if (count($vehicleIds) > 1) {
                    for ($i = 1; $i < count($vehicleIds); $i++) {
                        Payment::create([
                            'user_id' => $user->id,
                            'vehicle_id' => $vehicleIds[$i],
                            'type' => 'sticker_fee',
                            'status' => 'pending',
                            'amount' => 15.00,
                            'reference' => 'STK-'.strtoupper(uniqid()),
                            'batch_id' => $batchId,
                            'vehicle_count' => 1,
                        ]);
                    }
                }

                $payment->load(['user', 'vehicle.type', 'batchVehicles']);
                broadcast(new PaymentUpdated($payment, 'created', auth()->user()->first_name.' '.auth()->user()->last_name));
            }

            // Broadcast user update
            broadcast(new UserUpdated($user, 'created', 'admin'));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student registered successfully!',
                'user_id' => $user->id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Student registration failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if email is available
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Email is already registered' : 'Email is available',
        ]);
    }

    /**
     * Check if student ID is available
     */
    public function checkStudentId(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'string'],
        ]);

        $exists = \App\Models\Student::where('student_id', $request->student_id)->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Student ID is already registered' : 'Student ID is available',
        ]);
    }

    /**
     * Check if license number is available
     */
    public function checkLicenseNo(Request $request)
    {
        $request->validate([
            'license_no' => ['required', 'string'],
        ]);

        $exists = \App\Models\Student::where('license_no', $request->license_no)->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'License number is already registered' : 'License number is available',
        ]);
    }

    /**
     * Check if plate number is available
     */
    public function checkPlateNo(Request $request)
    {
        $request->validate([
            'plate_no' => ['required', 'string'],
        ]);

        $exists = \App\Models\Vehicle::where('plate_no', $request->plate_no)->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Plate number is already registered' : 'Plate number is available',
        ]);
    }
}
