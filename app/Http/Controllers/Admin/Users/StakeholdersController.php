<?php

namespace App\Http\Controllers\Admin\Users;

use App\Events\StakeholderUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Stakeholder;
use App\Models\StakeholderType;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationService;
use App\Services\PaymentBatchService;
use App\Services\StaticDataCacheService;
use App\Services\StickerGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StakeholdersController extends Controller
{
    /**
     * Show the stakeholders page.
     */
    public function index()
    {
        // Get all stakeholders with their relationships
        $stakeholders = Stakeholder::with(['user', 'type', 'vehicles.type'])
            ->latest()
            ->paginate(10);

        $vehicleTypes = StaticDataCacheService::getVehicleTypes();
        $stakeholderTypes = StaticDataCacheService::getStakeholderTypes();

        return view('admin.users.stakeholders', [
            'pageTitle' => 'Stakeholders Management',
            'stakeholders' => $stakeholders,
            'vehicleTypes' => $vehicleTypes,
            'stakeholderTypes' => $stakeholderTypes,
        ]);
    }

    /**
     * Get stakeholders data for AJAX requests
     */
    public function data()
    {
        $stakeholders = Stakeholder::with(['user', 'type', 'vehicles.type'])
            ->get()
            ->map(function ($stakeholder) {
                return [
                    'id' => $stakeholder->id,
                    'user_id' => $stakeholder->user_id,
                    'type_id' => $stakeholder->type_id,
                    'type_name' => $stakeholder->type ? $stakeholder->type->name : null,
                    'stakeholder_type' => $stakeholder->type ? [
                        'id' => $stakeholder->type->id,
                        'name' => $stakeholder->type->name,
                    ] : null,
                    'license_no' => $stakeholder->license_no,
                    'license_image' => $stakeholder->license_image ? '/storage/'.$stakeholder->license_image : null,
                    'user' => [
                        'id' => $stakeholder->user->id,
                        'first_name' => $stakeholder->user->first_name,
                        'last_name' => $stakeholder->user->last_name,
                        'email' => $stakeholder->user->email,
                        'is_active' => $stakeholder->user->is_active,
                    ],
                    'vehicles' => $stakeholder->vehicles->map(function ($vehicle) {
                        return [
                            'id' => $vehicle->id,
                            'type_id' => $vehicle->type_id,
                            'type_name' => $vehicle->type ? $vehicle->type->name : null,
                            'plate_no' => $vehicle->plate_no,
                            'color' => $vehicle->color,
                            'number' => $vehicle->number,
                            'sticker_image' => $vehicle->sticker,
                        ];
                    }),
                    'created_at' => $stakeholder->created_at,
                    'updated_at' => $stakeholder->updated_at,
                ];
            });

        return response()->json($stakeholders);
    }

    /**
     * Update the specified stakeholder.
     */
    public function update(Request $request, Stakeholder $stakeholder)
    {
        // Check authorization: Only Global Admin or Security Admin can update stakeholders
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$stakeholder->user_id],
            'type_id' => ['required', 'exists:stakeholder_types,id'],
            'license_no' => ['required', 'string', 'max:255', 'unique:stakeholders,license_no,'.$stakeholder->id],
            'is_active' => ['required', 'boolean'],
            'vehicles' => ['nullable', 'array', 'max:3'],
            'vehicles.*.type_id' => ['required', 'exists:vehicle_types,id'],
            'vehicles.*.plate_no' => ['nullable', 'string', 'max:255'],
            'vehicles_to_delete' => ['nullable', 'array'],
            'vehicles_to_delete.*' => ['integer', 'exists:vehicles,id'],
            'license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,heic,heif', 'max:5120'], // 5MB max
        ]);

        // Additional validation for plate numbers - check for duplicates
        if ($request->has('vehicles')) {
            $vehiclesArray = is_string($request->vehicles) ? json_decode($request->vehicles, true) : $request->vehicles;

            if ($vehiclesArray && ! empty($vehiclesArray)) {
                $plateNumbers = array_filter(array_column($vehiclesArray, 'plate_no'));
                $uniquePlateNumbers = array_unique($plateNumbers);

                if (count($plateNumbers) !== count($uniquePlateNumbers)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Duplicate plate numbers are not allowed.',
                    ], 422);
                }

                // Check if plate numbers already exist in the database (excluding soft-deleted vehicles)
                foreach ($vehiclesArray as $vehicleData) {
                    if (! empty($vehicleData['plate_no'])) {
                        $existingVehicle = \App\Models\Vehicle::withTrashed()
                            ->where('plate_no', $vehicleData['plate_no'])
                            ->where('user_id', '!=', $stakeholder->user_id)
                            ->first();

                        if ($existingVehicle) {
                            return response()->json([
                                'success' => false,
                                'message' => "Plate number '{$vehicleData['plate_no']}' is already registered to another user.",
                            ], 422);
                        }
                    }
                }
            }
        }

        DB::transaction(function () use ($validated, $stakeholder, $request) {
            // Get vehicles data from request (could be JSON string from FormData)
            $vehiclesData = $request->has('vehicles') ?
                (is_string($request->vehicles) ? json_decode($request->vehicles, true) : $request->vehicles) :
                $validated['vehicles'] ?? [];

            $vehiclesToDelete = $request->has('vehicles_to_delete') ?
                (is_string($request->vehicles_to_delete) ? json_decode($request->vehicles_to_delete, true) : $request->vehicles_to_delete) :
                [];

            // Store old is_active status
            $oldIsActive = $stakeholder->user->is_active;

            // Update user
            $stakeholder->user->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
            ]);

            // Broadcast status change directly to the user if is_active changed
            if ($oldIsActive !== $validated['is_active']) {
                broadcast(new \App\Events\UserStatusChanged($stakeholder->user, $validated['is_active']));
            }

            // Handle license image upload if provided
            $licenseImageData = [];
            if ($request->hasFile('license_image')) {
                // Delete old license image if exists
                if ($stakeholder->license_image) {
                    $oldPath = storage_path('app/public/'.$stakeholder->license_image);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                // Store new license image (without optimization to avoid GD dependency)
                $file = $request->file('license_image');
                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('licenses', $filename, 'public');
                $licenseImageData['license_image'] = $path;
            }

            // Update stakeholder
            $stakeholder->update(array_merge([
                'type_id' => $validated['type_id'],
                'license_no' => $validated['license_no'],
            ], $licenseImageData));

            // Delete vehicles marked for deletion via service
            if (! empty($vehiclesToDelete)) {
                $batchSvc = app(PaymentBatchService::class);
                $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                foreach ($vehiclesToDelete as $vehicleId) {
                    $vehicle = \App\Models\Vehicle::find($vehicleId);
                    if ($vehicle && $vehicle->user_id === $stakeholder->user_id) {
                        $batchSvc->removeVehicleFromBatch($vehicle->id, $editorName);
                        $vehicle->load(['user', 'type']);
                        broadcast(new VehicleUpdated($vehicle, 'deleted', $editorName));
                        $vehicle->delete();
                    }
                }
            }

            // Handle vehicles - only add new ones
            if (! empty($vehiclesData)) {
                // Add new vehicles only
                $stickerGenerator = new StickerGenerator;
                $newVehicleIds = [];

                // Get stakeholder type for sticker color
                $stakeholderType = StakeholderType::find($validated['type_id']);
                $stakeholderTypeName = $stakeholderType ? $stakeholderType->name : 'Guardian';

                foreach ($vehiclesData as $vehicleData) {
                    $plateNumber = $vehicleData['plate_no'] ?? null;
                    $typeId = $vehicleData['type_id'];

                    // Determine sticker color using StickerGenerator
                    $stickerColor = $stickerGenerator->determineStickerColor('stakeholder', $stakeholderTypeName, $plateNumber);

                    // Generate sticker number using StickerCounter
                    $stickerNumber = $stickerGenerator->generateNextStickerNumber($stickerColor);

                    // Create vehicle first
                    $vehicle = $stakeholder->vehicles()->create([
                        'type_id' => $typeId,
                        'plate_no' => $plateNumber,
                        'color' => $stickerColor,
                        'number' => $stickerNumber,
                    ]);

                    // Generate sticker image
                    $stickerPath = $stickerGenerator->generateVehicleSticker(
                        $stickerNumber,
                        'stakeholder',
                        $plateNumber,
                        $stickerColor,
                        $vehicle->id
                    );

                    // Update vehicle with sticker data
                    $vehicle->update([
                        'number' => $stickerNumber,
                        'sticker' => $stickerPath,
                    ]);

                    // Broadcast vehicle creation
                    $vehicle->load(['user', 'type']);
                    broadcast(new VehicleUpdated($vehicle, 'created', auth()->user()->first_name.' '.auth()->user()->last_name));

                    // Track new vehicle IDs for payment creation
                    $newVehicleIds[] = $vehicle->id;
                }

                // Create pending payments for new vehicles via service
                if (count($newVehicleIds) > 0) {
                    $batchSvc = app(PaymentBatchService::class);
                    $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                    $batchSvc->addVehiclesToBatch($stakeholder->user_id, $newVehicleIds, $editorName);
                }
            }

            // Broadcast the event with fresh relationships
            broadcast(new StakeholderUpdated($stakeholder->fresh(['user', 'type', 'vehicles.type']), 'updated', auth()->user()));

            // Notify administrators (exclude actor)
            $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
            $stakeholderName = $stakeholder->user->first_name.' '.$stakeholder->user->last_name;
            app(NotificationService::class)->notifyAdmins(
                'stakeholder_updated',
                'Stakeholder Updated',
                "{$editorName} updated Stakeholder {$stakeholderName}",
                [
                    'stakeholder_id' => $stakeholder->id,
                    'action' => 'updated',
                    'url' => '/users/stakeholders',
                ],
                auth()->id()
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Stakeholder updated successfully!',
            'editor' => auth()->user()->first_name.' '.auth()->user()->last_name,
        ]);
    }

    /**
     * Remove the specified stakeholder.
     */
    public function destroy(Stakeholder $stakeholder)
    {
        // Check authorization: Only Global Admin or Security Admin can delete stakeholders
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
        $stakeholderName = $stakeholder->user->first_name.' '.$stakeholder->user->last_name;

        DB::transaction(function () use ($stakeholder, $editorName, $stakeholderName) {
            // Broadcast the event before deletion
            broadcast(new StakeholderUpdated($stakeholder, 'deleted', auth()->user()));

            // Notify administrators (exclude actor)
            app(\App\Services\NotificationService::class)->notifyAdmins(
                'stakeholder_deleted',
                'Stakeholder Removed',
                "{$editorName} removed {$stakeholderName}",
                [
                    'action' => 'deleted',
                    'url' => '/users/stakeholders',
                ],
                auth()->id()
            );

            // Delete stakeholder and user
            $stakeholder->user->delete(); // This will cascade delete the stakeholder and vehicles
        });

        return response()->json([
            'success' => true,
            'message' => 'Stakeholder deleted successfully!',
        ]);
    }
}
