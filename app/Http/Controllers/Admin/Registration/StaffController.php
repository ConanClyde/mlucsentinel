<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Events\StaffUpdated;
use App\Events\UserUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\IdempotencyService;
use App\Services\PaymentBatchService;
use App\Services\StaticDataCacheService;
use App\Services\StickerGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    protected $stickerGenerator;

    public function __construct(StickerGenerator $stickerGenerator)
    {
        $this->stickerGenerator = $stickerGenerator;
    }

    /**
     * Show the staff registration page.
     */
    public function index()
    {
        $vehicleTypes = StaticDataCacheService::getVehicleTypes();

        return view('admin.registration.staff', [
            'pageTitle' => 'Staff Registration',
            'vehicleTypes' => $vehicleTypes,
        ]);
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        // Check authorization: Only Global Admin or Security Admin can register staff
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        try {
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph)$/'],
                'staff_id' => ['required', 'string', 'max:255', 'unique:staff,staff_id'],
                'license_no' => [
                    'nullable',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        if ($value && \App\Models\Staff::where('license_no', $value)->exists()) {
                            $fail('The license number has already been taken.');
                        }
                    },
                ],
                'license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,heic,heif', 'max:5120'],
                'vehicles' => ['required', 'array', 'min:1', 'max:3'],
                'vehicles.*.type_id' => ['required', 'exists:vehicle_types,id'],
                'vehicles.*.plate_no' => ['nullable', 'string', 'max:255'],
            ], [
                'email.unique' => 'Email is already registered',
                'email.regex' => 'Email must be from Gmail (@gmail.com) or DMMMSU (@dmmmsu.edu.ph)',
                'staff_id.unique' => 'Staff ID is already registered',
                'license_no.unique' => 'License number is already registered',
                'vehicles.max' => 'Maximum of 3 vehicles allowed per staff member',
            ]);

            // Idempotency: prevent duplicate submissions
            if ($key = $request->header('Idempotency-Key')) {
                $routeName = $request->route() ? $request->route()->getName() : 'admin.registration.staff.store';
                $ok = app(IdempotencyService::class)->ensure($key, auth()->id(), $routeName, [
                    'email' => $request->email,
                    'staff_id' => $request->staff_id,
                    'vehicles' => $request->vehicles,
                ]);
                if (! $ok) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Duplicate registration ignored.',
                    ], 200);
                }
            }

            // Custom validation for plate numbers
            foreach ($request->vehicles as $index => $vehicle) {
                $typeId = $vehicle['type_id'];
                $plateNo = $vehicle['plate_no'] ?? '';

                // Get vehicle type to check if it requires plate number
                $vehicleType = $typeId ? \App\Models\VehicleType::find($typeId) : null;

                // Plate number is required if vehicle type requires it
                if ($vehicleType && $vehicleType->requires_plate && empty($plateNo)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Plate number is required for {$vehicleType->name} vehicles.",
                        'errors' => ["vehicles.{$index}.plate_no" => ["Plate number is required for {$vehicleType->name} vehicles."]],
                    ], 422);
                }

                // Validate plate number format for vehicles that require plates
                if ($vehicleType && $vehicleType->requires_plate && ! empty($plateNo)) {
                    if (! preg_match('/^[A-Z]{2,3}-[0-9]{3,4}$/', $plateNo)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Plate number must be in format ABC-1234 (2-3 letters, dash, 3-4 numbers)',
                            'errors' => ["vehicles.{$index}.plate_no" => ['Plate number must be in format ABC-1234 (2-3 letters, dash, 3-4 numbers)']],
                        ], 422);
                    }

                    // Check if plate number is unique
                    $plateExists = Vehicle::where('plate_no', $plateNo)->exists();
                    if ($plateExists) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Plate number is already registered',
                            'errors' => ["vehicles.{$index}.plate_no" => ['Plate number is already registered']],
                        ], 422);
                    }
                }
            }

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make('temp_password_'.time()), // Temporary password
                'user_type' => 'staff',
                'is_active' => true,
            ]);

            // Handle license image upload (store without optimization to avoid GD dependency)
            $licenseImagePath = null;
            if ($request->hasFile('license_image')) {
                $file = $request->file('license_image');
                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $licenseImagePath = $file->storeAs('licenses', $filename, 'public');
            }

            // Create staff record
            $staff = Staff::create([
                'user_id' => $user->id,
                'staff_id' => $request->staff_id,
                'license_no' => $request->license_no,
                'license_image' => $licenseImagePath,
                'expiration_date' => now()->addYears(4), // 4 years from now
            ]);

            // Create vehicles and generate stickers (MAROON for staff)
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

                // Staff always gets maroon stickers
                $color = 'maroon';
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

            // Create batched payment using service to enforce single representative per batch
            if (count($vehicleIds) > 0) {
                $batchSvc = app(PaymentBatchService::class);
                $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                $batchSvc->addVehiclesToBatch($user->id, $vehicleIds, $editorName);
            }

            // Broadcast events
            broadcast(new UserUpdated($user, 'created', 'admin'));
            broadcast(new StaffUpdated($staff, 'created', auth()->user()));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Staff registered successfully!',
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
            \Log::error('Staff registration failed: '.$e->getMessage(), [
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
     * Check if staff ID is available
     */
    public function checkStaffId(Request $request)
    {
        $request->validate([
            'staff_id' => ['required', 'string'],
        ]);

        $exists = Staff::where('staff_id', $request->staff_id)->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Staff ID is already registered' : 'Staff ID is available',
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

        $exists = Staff::where('license_no', $request->license_no)->exists();

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

        $exists = Vehicle::where('plate_no', $request->plate_no)->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Plate number is already registered' : 'Plate number is available',
        ]);
    }
}
