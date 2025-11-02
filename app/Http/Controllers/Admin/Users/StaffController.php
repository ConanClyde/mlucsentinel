<?php

namespace App\Http\Controllers\Admin\Users;

use App\Events\NotificationCreated;
use App\Events\StaffUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\StaticDataCacheService;
use App\Services\StickerGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Show the staff page.
     */
    public function index()
    {
        // Get all staff with their relationships
        $staff = Staff::with(['user', 'vehicles.type'])
            ->latest()
            ->paginate(10);

        $vehicleTypes = StaticDataCacheService::getVehicleTypes();

        return view('admin.users.staff', [
            'pageTitle' => 'Staff Management',
            'staff' => $staff,
            'vehicleTypes' => $vehicleTypes,
        ]);
    }

    /**
     * Get staff data for AJAX requests
     */
    public function data()
    {
        $staff = Staff::with(['user', 'vehicles.type'])
            ->get()
            ->map(function ($staffMember) {
                return [
                    'id' => $staffMember->id,
                    'user_id' => $staffMember->user_id,
                    'staff_id' => $staffMember->staff_id,
                    'license_no' => $staffMember->license_no,
                    'license_image' => $staffMember->license_image ? '/storage/'.$staffMember->license_image : null,
                    'user' => [
                        'id' => $staffMember->user->id,
                        'first_name' => $staffMember->user->first_name,
                        'last_name' => $staffMember->user->last_name,
                        'email' => $staffMember->user->email,
                        'is_active' => $staffMember->user->is_active,
                    ],
                    'vehicles' => $staffMember->vehicles->map(function ($vehicle) {
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
                    'created_at' => $staffMember->created_at,
                    'updated_at' => $staffMember->updated_at,
                ];
            });

        return response()->json($staff);
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$staff->user_id],
            'staff_id' => ['required', 'string', 'max:255', 'unique:staff,staff_id,'.$staff->id],
            'license_no' => ['required', 'string', 'max:255', 'unique:staff,license_no,'.$staff->id],
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
                            ->where('user_id', '!=', $staff->user_id)
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

        DB::transaction(function () use ($validated, $staff, $request) {
            // Get vehicles data from request (could be JSON string from FormData)
            $vehiclesData = $request->has('vehicles') ?
                (is_string($request->vehicles) ? json_decode($request->vehicles, true) : $request->vehicles) :
                $validated['vehicles'] ?? [];

            $vehiclesToDelete = $request->has('vehicles_to_delete') ?
                (is_string($request->vehicles_to_delete) ? json_decode($request->vehicles_to_delete, true) : $request->vehicles_to_delete) :
                [];

            // Update user
            $staff->user->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
            ]);

            // Handle license image upload if provided
            $licenseImageData = [];
            if ($request->hasFile('license_image')) {
                // Delete old license image if exists
                if ($staff->license_image) {
                    $oldPath = storage_path('app/public/'.$staff->license_image);
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

            // Update staff
            $staff->update(array_merge([
                'staff_id' => $validated['staff_id'],
                'license_no' => $validated['license_no'],
            ], $licenseImageData));

            // Delete vehicles marked for deletion
            if (! empty($vehiclesToDelete)) {
                foreach ($vehiclesToDelete as $vehicleId) {
                    $vehicle = \App\Models\Vehicle::find($vehicleId);
                    if ($vehicle && $vehicle->user_id === $staff->user_id) {
                        // Delete associated pending payment if exists
                        $pendingPayment = \App\Models\Payment::where('vehicle_id', $vehicle->id)
                            ->where('status', 'pending')
                            ->first();

                        if ($pendingPayment) {
                            // Check if this payment is part of a batch
                            if ($pendingPayment->batch_id) {
                                // Get all payments in this batch
                                $batchPayments = \App\Models\Payment::where('batch_id', $pendingPayment->batch_id)
                                    ->where('status', 'pending')
                                    ->where('id', '!=', $pendingPayment->id)
                                    ->get();

                                // Delete the payment for this specific vehicle
                                $pendingPayment->delete();

                                // Update vehicle_count for remaining payments in the batch
                                if ($batchPayments->isNotEmpty()) {
                                    $newVehicleCount = $batchPayments->count();

                                    \App\Models\Payment::where('batch_id', $pendingPayment->batch_id)
                                        ->where('status', 'pending')
                                        ->update(['vehicle_count' => $newVehicleCount]);

                                    // Broadcast payment update for real-time updates
                                    $updatedPayment = $batchPayments->first()->fresh(['user', 'vehicle.type']);
                                    broadcast(new \App\Events\PaymentUpdated($updatedPayment, 'updated', auth()->user()->first_name.' '.auth()->user()->last_name));
                                }
                            } else {
                                // Single vehicle payment - delete the entire payment
                                $pendingPayment->delete();

                                // Broadcast payment deletion
                                broadcast(new \App\Events\PaymentUpdated($pendingPayment, 'deleted', auth()->user()->first_name.' '.auth()->user()->last_name));
                            }
                        }

                        // Broadcast vehicle deletion before deleting
                        $vehicle->load(['user', 'type']);
                        broadcast(new VehicleUpdated($vehicle, 'deleted', auth()->user()->first_name.' '.auth()->user()->last_name));
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

                    // Determine sticker color using StickerGenerator (staff gets maroon stickers)
                    $stickerColor = 'maroon'; // Staff always get maroon stickers

                    // Generate sticker number using StickerCounter
                    $stickerNumber = $stickerGenerator->generateNextStickerNumber($stickerColor);

                    // Create vehicle first
                    $vehicle = $staff->vehicles()->create([
                        'type_id' => $typeId,
                        'plate_no' => $plateNumber,
                        'color' => $stickerColor,
                        'number' => $stickerNumber,
                    ]);

                    // Generate sticker image
                    $stickerPath = $stickerGenerator->generateVehicleSticker(
                        $stickerNumber,
                        'staff',
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

                // Create pending payments for new vehicles (batched if multiple)
                if (count($newVehicleIds) > 0) {
                    $batchId = count($newVehicleIds) > 1 ? 'BATCH-'.strtoupper(uniqid()) : null;
                    $totalAmount = count($newVehicleIds) * 15.00;

                    // Create main payment record
                    $payment = \App\Models\Payment::create([
                        'user_id' => $staff->user_id,
                        'vehicle_id' => $newVehicleIds[0],
                        'type' => 'sticker_fee',
                        'status' => 'pending',
                        'amount' => $totalAmount,
                        'reference' => 'STK-'.strtoupper(uniqid()),
                        'batch_id' => $batchId,
                        'vehicle_count' => count($newVehicleIds),
                    ]);

                    // Create child payment records for other vehicles
                    if (count($newVehicleIds) > 1) {
                        for ($i = 1; $i < count($newVehicleIds); $i++) {
                            \App\Models\Payment::create([
                                'user_id' => $staff->user_id,
                                'vehicle_id' => $newVehicleIds[$i],
                                'type' => 'sticker_fee',
                                'status' => 'pending',
                                'amount' => 15.00,
                                'reference' => 'STK-'.strtoupper(uniqid()),
                                'batch_id' => $batchId,
                                'vehicle_count' => 1,
                            ]);
                        }
                    }

                    // Broadcast payment creation (only broadcast the main payment)
                    $payment->load(['user', 'vehicle.type', 'batchVehicles']);
                    broadcast(new \App\Events\PaymentUpdated($payment, 'created', auth()->user()->first_name.' '.auth()->user()->last_name));
                }
            }

            // Broadcast the event with fresh relationships
            broadcast(new StaffUpdated($staff->fresh(['user', 'vehicles.type']), 'updated', auth()->user()));

            // Create notification for all administrators
            $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
            $staffName = $staff->user->first_name.' '.$staff->user->last_name;

            User::whereIn('user_type', ['global_administrator', 'administrator'])
                ->where('id', '!=', auth()->id())
                ->get()
                ->each(function ($user) use ($editorName, $staffName, $staff) {
                    $notification = Notification::create([
                        'user_id' => $user->id,
                        'type' => 'staff_updated',
                        'title' => 'Staff Updated',
                        'message' => "{$editorName} updated Staff {$staffName}",
                        'data' => [
                            'staff_id' => $staff->id,
                            'action' => 'updated',
                            'url' => '/users/staff',
                        ],
                    ]);

                    // Broadcast notification in real-time
                    broadcast(new NotificationCreated($notification));
                });
        });

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully!',
            'editor' => auth()->user()->first_name.' '.auth()->user()->last_name,
        ]);
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(Staff $staff)
    {
        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
        $staffName = $staff->user->first_name.' '.$staff->user->last_name;

        DB::transaction(function () use ($staff, $editorName, $staffName) {
            // Broadcast the event before deletion
            broadcast(new StaffUpdated($staff, 'deleted', auth()->user()));

            // Create notification for all administrators
            User::whereIn('user_type', ['global_administrator', 'administrator'])
                ->where('id', '!=', auth()->id())
                ->get()
                ->each(function ($user) use ($editorName, $staffName) {
                    $notification = Notification::create([
                        'user_id' => $user->id,
                        'type' => 'staff_deleted',
                        'title' => 'Staff Removed',
                        'message' => "{$editorName} removed {$staffName}",
                        'data' => [
                            'action' => 'deleted',
                            'url' => '/users/staff',
                        ],
                    ]);

                    // Broadcast notification in real-time
                    broadcast(new NotificationCreated($notification));
                });

            // Delete staff and user
            $staff->user->delete(); // This will cascade delete the staff and vehicles
        });

        return response()->json([
            'success' => true,
            'message' => 'Staff deleted successfully!',
        ]);
    }
}
