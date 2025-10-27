<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Events\PaymentUpdated;
use App\Events\SecurityUpdated;
use App\Events\UserUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Security;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
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
        $vehicleTypes = VehicleType::orderBy('name')->get();

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
        try {
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph)$/'],
                'security_id' => ['required', 'string', 'max:255', 'unique:security,security_id'],
                'license_no' => ['required', 'string', 'max:255', 'unique:security,license_no'],
                'license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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

            // Custom validation for plate numbers
            foreach ($request->vehicles as $index => $vehicle) {
                $typeId = $vehicle['type_id'];
                $plateNo = $vehicle['plate_no'] ?? '';

                // Plate number is required for Motorcycle (1) and Car (2), but not for Electric Vehicle (3)
                if ($typeId != 3 && empty($plateNo)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Plate number is required for Motorcycle and Car vehicles.',
                        'errors' => ["vehicles.{$index}.plate_no" => ['Plate number is required for Motorcycle and Car vehicles.']],
                    ], 422);
                }

                // Validate plate number format for non-electric vehicles
                if ($typeId != 3 && ! empty($plateNo)) {
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

            // Handle license image upload
            $licenseImagePath = null;
            if ($request->hasFile('license_image')) {
                $licenseImagePath = $request->file('license_image')->store('licenses', 'public');
            }

            // Create security record
            $security = Security::create([
                'user_id' => $user->id,
                'security_id' => $request->security_id,
                'license_no' => $request->license_no,
                'license_image' => $licenseImagePath,
                'expiration_date' => now()->addYears(4), // 4 years from now
            ]);

            // Create vehicles and generate stickers (GREEN for security)
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

                // Security always gets maroon stickers
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

            // Create batched payment for all vehicles
            if (count($vehicleIds) > 0) {
                $batchId = count($vehicleIds) > 1 ? 'BATCH-'.strtoupper(uniqid()) : null;
                $totalAmount = count($vehicleIds) * 15.00;

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
            \Log::error('Security registration failed: '.$e->getMessage());

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
