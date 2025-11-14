<?php

namespace App\Http\Controllers\Admin;

use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkVehiclesController extends Controller
{
    /**
     * Bulk update vehicles.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        if (! auth()->user()->hasPrivilege('edit_vehicles')) {
            abort(403, 'You do not have permission to edit vehicles.');
        }
        $request->validate([
            'vehicle_ids' => ['required', 'array', 'min:1'],
            'vehicle_ids.*' => ['required', 'integer', 'exists:vehicles,id'],
            'updates' => ['required', 'array'],
        ]);

        $vehicleIds = $request->input('vehicle_ids');
        $updates = $request->input('updates');
        $updatedCount = 0;
        $errors = [];

        DB::transaction(function () use ($vehicleIds, $updates, &$updatedCount, &$errors) {
            foreach ($vehicleIds as $vehicleId) {
                try {
                    $vehicle = Vehicle::find($vehicleId);
                    if (! $vehicle) {
                        $errors[] = "Vehicle ID {$vehicleId} not found";

                        continue;
                    }

                    // Update vehicle fields
                    if (isset($updates['type_id'])) {
                        $vehicle->type_id = $updates['type_id'];
                    }

                    if (isset($updates['is_active'])) {
                        $vehicle->is_active = filter_var($updates['is_active'], FILTER_VALIDATE_BOOLEAN);
                    }

                    $vehicle->save();

                    // Broadcast update
                    $vehicle->load(['user', 'type']);
                    $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                    broadcast(new VehicleUpdated($vehicle, 'updated', $editorName));

                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Vehicle ID {$vehicleId}: {$e->getMessage()}";
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Updated {$updatedCount} vehicle(s)",
            'data' => [
                'updated_count' => $updatedCount,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Bulk delete vehicles.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        if (! auth()->user()->hasPrivilege('delete_vehicles')) {
            abort(403, 'You do not have permission to delete vehicles.');
        }
        $request->validate([
            'vehicle_ids' => ['required', 'array', 'min:1'],
            'vehicle_ids.*' => ['required', 'integer', 'exists:vehicles,id'],
        ]);

        $vehicleIds = $request->input('vehicle_ids');
        $deletedCount = 0;
        $errors = [];

        DB::transaction(function () use ($vehicleIds, &$deletedCount, &$errors) {
            foreach ($vehicleIds as $vehicleId) {
                try {
                    $vehicle = Vehicle::with(['user', 'type'])->find($vehicleId);
                    if (! $vehicle) {
                        $errors[] = "Vehicle ID {$vehicleId} not found";

                        continue;
                    }

                    // Broadcast deletion
                    $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                    broadcast(new VehicleUpdated($vehicle, 'deleted', $editorName));

                    // Delete vehicle
                    $vehicle->delete();

                    $deletedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Vehicle ID {$vehicleId}: {$e->getMessage()}";
                }
            }
        });

        // Notify administrators
        $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
        app(NotificationService::class)->notifyAdmins(
            'bulk_vehicles_deleted',
            'Bulk Vehicles Deleted',
            "{$editorName} deleted {$deletedCount} vehicle(s)",
            [
                'action' => 'bulk_deleted',
                'count' => $deletedCount,
            ],
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deletedCount} vehicle(s)",
            'data' => [
                'deleted_count' => $deletedCount,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Bulk status update (activate/deactivate).
     */
    public function bulkStatusUpdate(Request $request): JsonResponse
    {
        if (! auth()->user()->hasPrivilege('edit_vehicles')) {
            abort(403, 'You do not have permission to edit vehicles.');
        }
        $request->validate([
            'vehicle_ids' => ['required', 'array', 'min:1'],
            'vehicle_ids.*' => ['required', 'integer', 'exists:vehicles,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $vehicleIds = $request->input('vehicle_ids');
        $isActive = $request->input('is_active');
        $updatedCount = 0;
        $errors = [];

        DB::transaction(function () use ($vehicleIds, $isActive, &$updatedCount, &$errors) {
            foreach ($vehicleIds as $vehicleId) {
                try {
                    $vehicle = Vehicle::find($vehicleId);
                    if (! $vehicle) {
                        $errors[] = "Vehicle ID {$vehicleId} not found";

                        continue;
                    }

                    $vehicle->is_active = $isActive;
                    $vehicle->save();

                    // Broadcast update
                    $vehicle->load(['user', 'type']);
                    $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
                    broadcast(new VehicleUpdated($vehicle, 'updated', $editorName));

                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Vehicle ID {$vehicleId}: {$e->getMessage()}";
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Updated status for {$updatedCount} vehicle(s)",
            'data' => [
                'updated_count' => $updatedCount,
                'errors' => $errors,
            ],
        ]);
    }
}
