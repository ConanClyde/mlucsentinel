<?php

namespace App\Http\Controllers\Admin\Registration;

use App\Events\PaymentUpdated;
use App\Events\StakeholderUpdated;
use App\Events\UserUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Stakeholder;
use App\Models\StakeholderType;
use App\Models\User;
use App\Models\Vehicle;
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
                    $typeId = $vehicleData['type_id'];
                    $plateNumber = $vehicleData['plate_no'] ?? null;

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
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during registration: '.$e->getMessage(),
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
