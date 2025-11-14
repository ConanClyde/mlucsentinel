<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Events\SecurityUpdated;
use App\Events\UserUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Security;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\IdempotencyService;
use App\Services\PaymentBatchService;
use App\Services\StaticDataCacheService;
use App\Services\StickerGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    protected $stickerGenerator;

    public function __construct(StickerGenerator $stickerGenerator)
    {
        $this->stickerGenerator = $stickerGenerator;
    }

    /**
     * Show the security registration page.
     */
    public function index()
    {
        $vehicleTypes = StaticDataCacheService::getVehicleTypes();

        return view('admin.registration.security', [
            'pageTitle' => 'Security Registration',
            'vehicleTypes' => $vehicleTypes,
        ]);
    }

    /**
     * Store a newly created security member.
     */
    public function store(Request $request)
    {
        // Check authorization: Only Global Admin or Security Admin can register security
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        try {
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph)$/'],
                'security_id' => ['required', 'string', 'max:255', 'unique:security,security_id'],
                'license_no' => [
                    'nullable',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        if ($value && \App\Models\Security::where('license_no', $value)->exists()) {
                            $fail('The license number has already been taken.');
                        }
                    },
                ],
                'license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,heic,heif', 'max:5120'],
                'vehicles' => ['required', 'array', 'min:1', 'max:3'],
                'vehicles.*.type_id' => ['required', 'exists:vehicle_types,id'],
                'vehicles.*.plate_no' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required'],
            ], [
                'email.unique' => 'Email is already registered',
                'email.regex' => 'Email must be from Gmail (@gmail.com) or DMMMSU (@dmmmsu.edu.ph)',
                'security_id.unique' => 'Security ID is already registered',
                'license_no.unique' => 'License number is already registered',
                'vehicles.max' => 'Maximum of 3 vehicles allowed per security member',
                'password.min' => 'Password must be at least 8 characters',
                'password.confirmed' => 'Password confirmation does not match',
            ]);

            // Idempotency: prevent duplicate submissions
            if ($key = $request->header('Idempotency-Key')) {
                $routeName = $request->route() ? $request->route()->getName() : 'admin.registration.security.store';
                $ok = app(IdempotencyService::class)->ensure($key, auth()->id(), $routeName, [
                    'email' => $request->email,
                    'security_id' => $request->security_id,
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

            // Create user with provided password
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'security',
                'is_active' => true,
            ]);

            // Handle license image upload (store without optimization to avoid GD dependency)
            $licenseImagePath = null;
            if ($request->hasFile('license_image')) {
                $file = $request->file('license_image');
                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $licenseImagePath = $file->storeAs('licenses', $filename, 'public');
            }

            // Create security record
            $rules = \App\Models\StickerRule::getSingleton();
            $years = (int) ($rules->security_expiration_years ?? 4);
            $security = Security::create([
                'user_id' => $user->id,
                'security_id' => $request->security_id,
                'license_no' => $request->license_no,
                'license_image' => $licenseImagePath,
                'expiration_date' => now()->addYears($years),
            ]);

            // Create vehicles and generate stickers (GREEN for security)
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

                // Security color from settings
                $color = $this->stickerGenerator->determineStickerColor('security', null, $plateNumber);
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

            // Create batched payment using the service to enforce single representative per batch
            if (count($vehicleIds) > 0) {
                $batchSvc = app(PaymentBatchService::class);
                $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                $batchSvc->addVehiclesToBatch($user->id, $vehicleIds, $editorName);
            }

            // Broadcast events
            broadcast(new UserUpdated($user, 'created', 'admin'));
            broadcast(new SecurityUpdated($security, 'created', auth()->user()));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Security registered successfully!',
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
            \Log::error('Security registration failed: '.$e->getMessage(), [
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
     * Check if security ID is available
     */
    public function checkSecurityId(Request $request)
    {
        $request->validate([
            'security_id' => ['required', 'string'],
        ]);

        $exists = Security::where('security_id', $request->security_id)->exists();

        return response()->json([
            'available' => ! $exists,
            'message' => $exists ? 'Security ID is already registered' : 'Security ID is available',
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

        $exists = Security::where('license_no', $request->license_no)->exists();

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
