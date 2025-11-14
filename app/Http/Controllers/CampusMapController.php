<?php

namespace App\Http\Controllers;

use App\Models\MapLocation;
use App\Models\MapLocationType;
use Illuminate\View\View;

class CampusMapController extends Controller
{
    /**
     * Display the campus map (view-only for regular users).
     */
    public function index(): View
    {
        // Get active location types
        $locationTypes = MapLocationType::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        // Get active locations with their types
        $locations = MapLocation::with('type')
            ->active()
            ->ordered()
            ->get();

        return view('campus-map', [
            'pageTitle' => 'Campus Map',
            'viewOnly' => true, // Indicate this is view-only mode
            'locationTypes' => $locationTypes,
            'locations' => $locations,
        ]);
    }
}
