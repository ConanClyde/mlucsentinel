<?php

namespace App\Http\Controllers\Admin\Users;

use App\Events\SecurityUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Security;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationService;
use App\Services\PaymentBatchService;
use App\Services\StaticDataCacheService;
use App\Services\StickerGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    /**
     * Show the security page.
     */
    public function index()
    {
        // Get all security personnel with their relationships
        $security = Security::with(['user', 'vehicles.type'])
            ->latest()
            ->paginate(10);

        $vehicleTypes = StaticDataCacheService::getVehicleTypes();

        return view('admin.users.security', [
            'pageTitle' => 'Security Management',
            'security' => $security,
            'vehicleTypes' => $vehicleTypes,
        ]);
    }

    /**
     * Get security data for AJAX requests
     */
    public function data()
    {
        $security = Security::with(['user', 'vehicles.type'])
            ->get()
            ->map(function ($securityMember) {
                return [
                    'id' => $securityMember->id,
                    'user_id' => $securityMember->user_id,
                    'security_id' => $securityMember->security_id,
                    'license_no' => $securityMember->license_no,
                    'license_image' => $securityMember->license_image ? '/storage/'.$securityMember->license_image : null,
                    'user' => [
                        'id' => $securityMember->user->id,
                        'first_name' => $securityMember->user->first_name,
                        'last_name' => $securityMember->user->last_name,
                        'email' => $securityMember->user->email,
                        'is_active' => $securityMember->user->is_active,
                    ],
                    'vehicles' => $securityMember->vehicles->map(function ($vehicle) {
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
                    'created_at' => $securityMember->created_at,
                    'updated_at' => $securityMember->updated_at,
                ];
            });

        return response()->json($security);
    }

    /**
     * Update the specified security member.
     */
    public function update(Request $request, Security $security)
    {
        // Check authorization: Only Global Admin or Security Admin can update security
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$security->user_id],
            'security_id' => ['required', 'string', 'max:255', 'unique:security,security_id,'.$security->id],
            'license_no' => ['required', 'string', 'max:255', 'unique:security,license_no,'.$security->id],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
            'password_confirmation' => ['nullable', 'string', 'min:8', 'required_with:password', 'same:password'],
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
                            ->where('user_id', '!=', $security->user_id)
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

        DB::transaction(function () use ($validated, $security, $request) {
            // Get vehicles data from request (could be JSON string from FormData)
            $vehiclesData = $request->has('vehicles') ?
                (is_string($request->vehicles) ? json_decode($request->vehicles, true) : $request->vehicles) :
                $validated['vehicles'] ?? [];

            $vehiclesToDelete = $request->has('vehicles_to_delete') ?
                (is_string($request->vehicles_to_delete) ? json_decode($request->vehicles_to_delete, true) : $request->vehicles_to_delete) :
                [];

            // Store old is_active status
            $oldIsActive = $security->user->is_active;

            // Update user
            $userData = [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
            ];

            // Update password if provided
            if (! empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $security->user->update($userData);

            // Broadcast status change directly to the user if is_active changed
            if ($oldIsActive !== $validated['is_active']) {
                broadcast(new \App\Events\UserStatusChanged($security->user, $validated['is_active']));
            }

            // Handle license image upload if provided
            $licenseImageData = [];
            if ($request->hasFile('license_image')) {
                // Delete old license image if exists
                if ($security->license_image) {
                    $oldPath = storage_path('app/public/'.$security->license_image);
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

            // Update security
            $security->update(array_merge([
                'security_id' => $validated['security_id'],
                'license_no' => $validated['license_no'],
            ], $licenseImageData));

            // Delete vehicles marked for deletion via service
            if (! empty($vehiclesToDelete)) {
                $batchSvc = app(PaymentBatchService::class);
                $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                foreach ($vehiclesToDelete as $vehicleId) {
                    $vehicle = \App\Models\Vehicle::find($vehicleId);
                    if ($vehicle && $vehicle->user_id === $security->user_id) {
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

                foreach ($vehiclesData as $vehicleData) {
                    $plateNumber = $vehicleData['plate_no'] ?? null;
                    $typeId = $vehicleData['type_id'];

                    // Determine sticker color using StickerGenerator (security gets maroon stickers)
                    $stickerColor = 'maroon'; // Security always get maroon stickers

                    // Generate sticker number using StickerCounter
                    $stickerNumber = $stickerGenerator->generateNextStickerNumber($stickerColor);

                    // Create vehicle first
                    $vehicle = $security->vehicles()->create([
                        'type_id' => $typeId,
                        'plate_no' => $plateNumber,
                        'color' => $stickerColor,
                        'number' => $stickerNumber,
                    ]);

                    // Generate sticker image
                    $stickerPath = $stickerGenerator->generateVehicleSticker(
                        $stickerNumber,
                        'security',
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
                    $batchSvc->addVehiclesToBatch($security->user_id, $newVehicleIds, $editorName);
                }
            }

            // Broadcast the event with fresh relationships
            broadcast(new SecurityUpdated($security->fresh(['user', 'vehicles.type']), 'updated', auth()->user()));

            // Notify administrators (exclude actor)
            $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
            $securityName = $security->user->first_name.' '.$security->user->last_name;
            app(NotificationService::class)->notifyAdmins(
                'security_updated',
                'Security Updated',
                "{$editorName} updated Security {$securityName}",
                [
                    'security_id' => $security->id,
                    'action' => 'updated',
                    'url' => '/users/security',
                ],
                auth()->id()
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Security updated successfully!',
            'editor' => auth()->user()->first_name.' '.auth()->user()->last_name,
        ]);
    }

    /**
     * Remove the specified security member.
     */
    public function destroy(Security $security)
    {
        // Check authorization: Only Global Admin or Security Admin can delete security
        $user = auth()->user();
        if (! $user->isGlobalAdministrator() && ! $user->isSecurityAdmin()) {
            abort(403, 'Access denied. Global Administrator or Security Administrator access required.');
        }

        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
        $securityName = $security->user->first_name.' '.$security->user->last_name;

        DB::transaction(function () use ($security, $editorName, $securityName) {
            // Broadcast the event before deletion
            broadcast(new SecurityUpdated($security, 'deleted', auth()->user()));

            // Notify administrators (exclude actor)
            app(NotificationService::class)->notifyAdmins(
                'security_deleted',
                'Security Removed',
                "{$editorName} removed {$securityName}",
                [
                    'action' => 'deleted',
                    'url' => '/users/security',
                ],
                auth()->id()
            );

            // Delete security and user
            $security->user->delete(); // This will cascade delete the security and vehicles
        });

        return response()->json([
            'success' => true,
            'message' => 'Security deleted successfully!',
        ]);
    }
}
