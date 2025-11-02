<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMapLocationRequest;
use App\Models\MapLocation;
use App\Models\MapLocationType;
use Illuminate\Http\JsonResponse;

class MapLocationController extends Controller
{
    /**
     * Display the campus map page
     */
    public function index()
    {
        $locationTypes = MapLocationType::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $locations = MapLocation::with('type')
            ->active()
            ->ordered()
            ->get();

        return view('admin.campus-map', [
            'pageTitle' => 'Campus Map',
            'locationTypes' => $locationTypes,
            'locations' => $locations,
        ]);
    }

    /**
     * Get all active locations (API)
     */
    public function getLocations(): JsonResponse
    {
        $locations = MapLocation::with('type')
            ->active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    /**
     * Get all location types (API)
     */
    public function getTypes(): JsonResponse
    {
        $types = MapLocationType::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Store a new location
     */
    public function store(StoreMapLocationRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Use provided center_x/center_y if available, otherwise calculate from vertices
            if (! isset($data['center_x']) || ! isset($data['center_y'])) {
                $centerPoint = $this->calculateCenterPoint($data['vertices'] ?? []);
                $data['center_x'] = $centerPoint['x'];
                $data['center_y'] = $centerPoint['y'];
            }

            $location = MapLocation::create($data);
            $location->load('type');

            return response()->json([
                'success' => true,
                'message' => 'Location added successfully',
                'data' => $location,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create location: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a specific location
     */
    public function show(MapLocation $location): JsonResponse
    {
        $location->load('type');

        return response()->json([
            'success' => true,
            'data' => $location,
        ]);
    }

    /**
     * Update a location
     */
    public function update(StoreMapLocationRequest $request, MapLocation $location): JsonResponse
    {
        try {
            $data = $request->validated();

            // Use provided center_x/center_y if available, otherwise calculate from vertices
            if (! isset($data['center_x']) || ! isset($data['center_y'])) {
                $centerPoint = $this->calculateCenterPoint($data['vertices'] ?? []);
                $data['center_x'] = $centerPoint['x'];
                $data['center_y'] = $centerPoint['y'];
            }

            $location->update($data);
            $location->load('type');

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'data' => $location,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a location
     */
    public function destroy(MapLocation $location): JsonResponse
    {
        try {
            $location->delete();

            return response()->json([
                'success' => true,
                'message' => 'Location deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete location: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle location active status
     */
    public function toggleActive(MapLocation $location): JsonResponse
    {
        try {
            $location->is_active = ! $location->is_active;
            $location->save();

            return response()->json([
                'success' => true,
                'message' => 'Location status updated',
                'data' => $location,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate the center point (centroid) of a polygon from its vertices
     */
    private function calculateCenterPoint(array $vertices): array
    {
        if (empty($vertices)) {
            return ['x' => 0, 'y' => 0];
        }

        $sumX = 0;
        $sumY = 0;
        $count = count($vertices);

        foreach ($vertices as $vertex) {
            $sumX += $vertex['x'];
            $sumY += $vertex['y'];
        }

        return [
            'x' => round($sumX / $count, 4),
            'y' => round($sumY / $count, 4),
        ];
    }
}
