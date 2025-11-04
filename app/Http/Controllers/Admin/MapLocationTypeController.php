<?php

namespace App\Http\Controllers\Admin;

use App\Events\MapLocationTypeUpdated;
use App\Http\Controllers\Controller;
use App\Models\MapLocationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapLocationTypeController extends Controller
{
    /**
     * Display a listing of map location types.
     */
    public function index(): JsonResponse
    {
        $locationTypes = MapLocationType::orderBy('display_order')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $locationTypes,
        ]);
    }

    /**
     * Store a newly created map location type.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:map_location_types,name'],
            'default_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Always require polygon
        $validated['requires_polygon'] = true;
        if (! isset($validated['display_order'])) {
            $validated['display_order'] = 0;
        }
        if (! isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $locationType = MapLocationType::create($validated);

        // Get editor name
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Broadcast the event
        broadcast(new MapLocationTypeUpdated($locationType, 'created', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Location type created successfully',
            'data' => $locationType,
        ], 201);
    }

    /**
     * Update the specified map location type.
     */
    public function update(Request $request, MapLocationType $mapLocationType): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('map_location_types', 'name')->ignore($mapLocationType->id)],
            'default_color' => ['sometimes', 'required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // Always require polygon
        $validated['requires_polygon'] = true;

        $mapLocationType->update($validated);
        $mapLocationType->refresh();

        // Get editor name
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        // Broadcast the event
        broadcast(new MapLocationTypeUpdated($mapLocationType, 'updated', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Location type updated successfully',
            'data' => $mapLocationType,
        ]);
    }

    /**
     * Remove the specified map location type.
     */
    public function destroy(MapLocationType $mapLocationType): JsonResponse
    {
        // Check if location type has locations
        if ($mapLocationType->locations()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete location type with existing locations',
            ], 422);
        }

        // Get editor name before deleting
        $editor = auth()->user()->first_name.' '.auth()->user()->last_name;

        $mapLocationType->delete();

        // Broadcast the event
        broadcast(new MapLocationTypeUpdated($mapLocationType, 'deleted', $editor))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Location type deleted successfully',
        ]);
    }
}
