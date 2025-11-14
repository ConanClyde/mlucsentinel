<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Events\UserUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\IdempotencyService;
use App\Services\PaymentBatchService;
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
        $colleges = StaticDataCacheService::getColleges()->load('programs');
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
        // Check authorization: Only Global Admin or Security Admin can register students
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        try {
            $validated = $request->validated();

            // Idempotency: prevent duplicate submissions
            if ($key = $request->header('Idempotency-Key')) {
                $routeName = $request->route() ? $request->route()->getName() : 'admin.registration.student.store';
                $ok = app(IdempotencyService::class)->ensure($key, auth()->id(), $routeName, [
                    'email' => $request->email,
                    'student_id' => $request->student_id,
                    'vehicles' => $request->vehicles,
                ]);
                if (! $ok) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Duplicate registration ignored.',
                    ], 200);
                }
            }

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
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

            // Get program and its college
            $program = \App\Models\Program::findOrFail($request->program_id);

            // Create student record
            $rules = \App\Models\StickerRule::getSingleton();
            $years = (int) ($rules->student_expiration_years ?? 4);
            $student = Student::create([
                'user_id' => $user->id,
                'college_id' => $program->college_id,
                'program_id' => $request->program_id,
                'student_id' => $request->student_id,
                'license_no' => $request->license_no,
                'license_image' => $licenseImagePath,
                'expiration_date' => now()->addYears($years),
            ]);

            // Create vehicles and generate stickers
            $vehicleIds = [];
            foreach ($request->vehicles as $vehicleData) {
                $vehicleType = \App\Models\VehicleType::find($vehicleData['type_id']);
                $plateNumber = $vehicleData['plate_no'] ?? null;

                // If vehicle type doesn't require plate, set to null
                if ($vehicleType && ! $vehicleType->requires_plate) {
                    $plateNumber = null;
                } else {
                    // For vehicles that require plate, ensure plate_no is not empty string
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

            // Create batched payment using the service to enforce invariants
            if (count($vehicleIds) > 0) {
                $batchSvc = app(PaymentBatchService::class);
                $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                $batchSvc->addVehiclesToBatch($user->id, $vehicleIds, $editorName);
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
            \Log::error('Student registration failed: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
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
