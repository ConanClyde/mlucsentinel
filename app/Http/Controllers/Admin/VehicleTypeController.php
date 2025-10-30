<?php

namespace App\Http\Controllers\Admin;

use App\Events\VehicleTypeUpdated;
use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use App\Services\StaticDataCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of vehicle types.
     */
    public function index(): JsonResponse
    {
        $vehicleTypes = StaticDataCacheService::getVehicleTypes();

        return response()->json([
            'success' => true,
            'data' => $vehicleTypes,
        ]);
    }

    /**
     * Store a newly created vehicle type.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:vehicle_types,name'],
        ]);

        $vehicleType = VehicleType::create($validated);

        // Get editor name
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Broadcast the event
        broadcast(new VehicleTypeUpdated($vehicleType, 'created', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle type created successfully',
            'data' => $vehicleType,
        ], 201);
    }

    /**
     * Update the specified vehicle type.
     */
    public function update(Request $request, VehicleType $vehicleType): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('vehicle_types')->ignore($vehicleType->id)],
        ]);

        $vehicleType->update($validated);

        // Get editor name
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Broadcast the event
        broadcast(new VehicleTypeUpdated($vehicleType, 'updated', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle type updated successfully',
            'data' => $vehicleType,
        ]);
    }

    /**
     * Remove the specified vehicle type.
     */
    public function destroy(VehicleType $vehicleType): JsonResponse
    {
        // Check if vehicle type has vehicles
        if ($vehicleType->vehicles()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete vehicle type with existing vehicles',
            ], 422);
        }

        // Get editor name before deleting
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        $vehicleType->delete();

        // Broadcast the event
        broadcast(new VehicleTypeUpdated($vehicleType, 'deleted', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle type deleted successfully',
        ]);
    }
}
