<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Events\StakeholderUpdated;
use App\Events\UserUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Stakeholder;
use App\Models\StakeholderType;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\IdempotencyService;
use App\Services\PaymentBatchService;
use App\Services\StaticDataCacheService;
use App\Services\StickerGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StakeholderController extends Controller
{
    protected $stickerGenerator;

    public function __construct(StickerGenerator $stickerGenerator)
    {
        $this->stickerGenerator = $stickerGenerator;
    }

    /**
     * Show the stakeholder registration page.
     */
    public function index()
    {
        $vehicleTypes = StaticDataCacheService::getVehicleTypes();
        $stakeholderTypes = StaticDataCacheService::getStakeholderTypes();

        return view('admin.registration.stakeholder', [
            'pageTitle' => 'Stakeholder Registration',
            'vehicleTypes' => $vehicleTypes,
            'stakeholderTypes' => $stakeholderTypes,
        ]);
    }

    /**
     * Store a newly created stakeholder.
     */
    public function store(Request $request)
    {
        // Check authorization: Only Global Admin or Security Admin can register stakeholders
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        try {
            $request->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[^\s@]+@(gmail\.com|dmmmsu\.edu\.ph)$/'],
                'type_id' => ['required', 'exists:stakeholder_types,id'],
                'license_no' => [
                    'nullable',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        if ($value && \App\Models\Stakeholder::where('license_no', $value)->exists()) {
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
                'type_id.required' => 'Stakeholder type is required',
                'type_id.exists' => 'Invalid stakeholder type',
                'license_no.unique' => 'License number is already registered',
                'vehicles.max' => 'Maximum of 3 vehicles allowed per stakeholder',
            ]);

            // Idempotency: prevent duplicate submissions
            if ($key = $request->header('Idempotency-Key')) {
                $routeName = $request->route() ? $request->route()->getName() : 'admin.registration.stakeholder.store';
                $ok = app(IdempotencyService::class)->ensure($key, auth()->id(), $routeName, [
                    'email' => $request->email,
                    'type_id' => $request->type_id,
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

            try {
                // Create user
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'password' => Hash::make('password123'),
                    'user_type' => 'stakeholder',
                    'is_active' => true,
                ]);

                // Handle license image upload (store without optimization to avoid GD dependency)
                $licenseImagePath = null;
                if ($request->hasFile('license_image')) {
                    $file = $request->file('license_image');
                    $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                    $licenseImagePath = $file->storeAs('licenses', $filename, 'public');
                }

                // Create stakeholder
                $stakeholder = Stakeholder::create([
                    'user_id' => $user->id,
                    'type_id' => $request->type_id,
                    'license_no' => $request->license_no,
                    'license_image' => $licenseImagePath,
                    'expiration_date' => now()->addYears(4),
                ]);

                // Get stakeholder type for sticker color determination
                $stakeholderType = StakeholderType::find($request->type_id);
                $stakeholderTypeName = $stakeholderType ? $stakeholderType->name : 'Guardian';

                // Create vehicles with stickers
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

                    // Determine sticker color using StickerGenerator
                    $stickerColor = $this->stickerGenerator->determineStickerColor('stakeholder', $stakeholderTypeName, $plateNumber);

                    // Generate sticker number using StickerCounter
                    $stickerNumber = $this->stickerGenerator->generateNextStickerNumber($stickerColor);

                    // Create vehicle first
                    $vehicle = Vehicle::create([
                        'user_id' => $user->id,
                        'type_id' => $typeId,
                        'plate_no' => $plateNumber,
                        'color' => $stickerColor,
                        'number' => $stickerNumber,
                    ]);

                    // Generate sticker image
                    $stickerPath = $this->stickerGenerator->generateVehicleSticker(
                        $stickerNumber,
                        'stakeholder',
                        $plateNumber,
                        $stickerColor,
                        $vehicle->id
                    );

                    // Update vehicle with sticker path
                    $vehicle->update([
                        'sticker' => $stickerPath,
                    ]);

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

                DB::commit();

                // Broadcast the event
                broadcast(new UserUpdated($user, 'created', 'admin'));
                broadcast(new StakeholderUpdated($stakeholder, 'created', auth()->user()));

                return response()->json([
                    'success' => true,
                    'message' => 'Stakeholder registered successfully!',
                    'user' => $user,
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Stakeholder registration failed: '.$e->getMessage(), [
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
        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'available' => ! $exists,
        ]);
    }

    /**
     * Check if license number is available
     */
    public function checkLicenseNo(Request $request)
    {
        $exists = Stakeholder::where('license_no', $request->license_no)->exists();

        return response()->json([
            'available' => ! $exists,
        ]);
    }

    /**
     * Check if plate number is available
     */
    public function checkPlateNo(Request $request)
    {
        $exists = Vehicle::where('plate_no', $request->plate_no)->exists();

        return response()->json([
            'available' => ! $exists,
        ]);
    }
}
