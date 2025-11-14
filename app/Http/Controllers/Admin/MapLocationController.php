<?php

namespace App\Http\Controllers\Admin;

use App\Events\MapLocationUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMapLocationRequest;
use App\Models\MapLocation;
use App\Models\MapLocationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class MapLocationController extends Controller
{
    /**
     * Display the campus map page
     */
    public function index()
    {
        $user = auth()->user();
        if (! $user->hasPrivilege('manage_campus_map')) {
            abort(403, 'You do not have permission to view the campus map.');
        }
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
        if (! auth()->user()->hasPrivilege('manage_campus_map')) {
            abort(403, 'You do not have permission to view the campus map.');
        }
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
        if (! auth()->user()->hasPrivilege('manage_campus_map')) {
            abort(403, 'You do not have permission to view the campus map.');
        }
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
        if (! auth()->user()->hasPrivilege('manage_campus_map')) {
            abort(403, 'You do not have permission to modify the campus map.');
        }
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

            // Broadcast the event for real-time updates
            broadcast(new MapLocationUpdated($location, 'created'))->toOthers();

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
        if (! auth()->user()->hasPrivilege('manage_campus_map')) {
            abort(403, 'You do not have permission to view the campus map.');
        }
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
        if (! auth()->user()->hasPrivilege('manage_campus_map')) {
            abort(403, 'You do not have permission to modify the campus map.');
        }
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

            // Broadcast the event for real-time updates
            broadcast(new MapLocationUpdated($location->fresh(), 'updated'))->toOthers();

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
        if (! auth()->user()->hasPrivilege('manage_campus_map')) {
            abort(403, 'You do not have permission to modify the campus map.');
        }
        try {
            // Broadcast the event before deletion
            broadcast(new MapLocationUpdated($location, 'deleted'))->toOthers();

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
        if (! auth()->user()->hasPrivilege('manage_campus_map')) {
            abort(403, 'You do not have permission to modify the campus map.');
        }
        try {
            $location->is_active = ! $location->is_active;
            $location->save();
            $location->load('type');

            // Broadcast the event for real-time updates
            broadcast(new MapLocationUpdated($location, 'updated'))->toOthers();

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
     * Download all map location stickers as a ZIP file
     */
    public function downloadAllStickers()
    {
        if (! auth()->user()->hasPrivilege('manage_campus_map')) {
            abort(403, 'You do not have permission to modify the campus map.');
        }
        try {
            $locations = MapLocation::with('type')
                ->active()
                ->whereNotNull('sticker_path')
                ->ordered()
                ->get();

            if ($locations->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No stickers available for download',
                ], 404);
            }

            // Create a temporary zip file
            $zipFileName = 'map-location-stickers-'.date('Y-m-d-His').'.zip';
            $zipFilePath = storage_path('app/temp/'.$zipFileName);

            // Ensure temp directory exists
            if (! file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new ZipArchive;
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create zip file',
                ], 500);
            }

            $filesAdded = 0;
            foreach ($locations as $location) {
                if ($location->sticker_path) {
                    // Convert /storage/ path to actual file path
                    $stickerPath = str_replace('/storage/', '', $location->sticker_path);
                    $fullPath = Storage::disk('public')->path($stickerPath);

                    if (file_exists($fullPath)) {
                        // Add file to zip with a descriptive name
                        $fileName = $location->short_code.'_'.$location->name.'.svg';
                        // Sanitize filename
                        $fileName = preg_replace('/[^A-Za-z0-9_.-]/', '_', $fileName);
                        $zip->addFile($fullPath, $fileName);
                        $filesAdded++;
                    }
                }
            }
            $zip->close();

            if ($filesAdded === 0) {
                // Clean up empty zip file
                if (file_exists($zipFilePath)) {
                    unlink($zipFilePath);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'No sticker files found to download',
                ], 404);
            }

            // Download and then delete the temp file
            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Failed to download map stickers: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to download stickers: '.$e->getMessage(),
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
