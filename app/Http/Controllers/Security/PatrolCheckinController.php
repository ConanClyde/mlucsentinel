<?php

namespace App\Http\Controllers\Security;

use App\Events\PatrolLogCreated;
use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use App\Models\PatrolLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatrolCheckinController extends Controller
{
    /**
     * Display the QR scanner page
     */
    public function scanner(): View
    {
        // Get user's recent check-ins (last 5)
        $recentCheckins = PatrolLog::where('security_user_id', auth()->id())
            ->with('mapLocation')
            ->latest('checked_in_at')
            ->take(5)
            ->get();

        return view('security.patrol-scanner', [
            'pageTitle' => 'Patrol Scanner',
            'recentCheckins' => $recentCheckins,
        ]);
    }

    /**
     * Display the patrol check-in page
     */
    public function show(Request $request): View|RedirectResponse
    {
        $locationId = $request->query('location');

        if (! $locationId) {
            return redirect()->route('home')->with('error', 'No location specified for patrol check-in');
        }

        $location = MapLocation::with('type')->find($locationId);

        if (! $location) {
            return redirect()->route('home')->with('error', 'Location not found');
        }

        // Get user's recent check-ins at this location (last 24 hours)
        $recentCheckins = PatrolLog::where('security_user_id', auth()->id())
            ->where('map_location_id', $locationId)
            ->where('checked_in_at', '>=', now()->subHours(24))
            ->latest('checked_in_at')
            ->take(5)
            ->get();

        // Get last check-in at this location by any guard
        $lastCheckin = PatrolLog::where('map_location_id', $locationId)
            ->with('securityUser')
            ->latest('checked_in_at')
            ->first();

        return view('security.patrol-checkin', [
            'pageTitle' => 'Patrol Check-In',
            'location' => $location,
            'recentCheckins' => $recentCheckins,
            'lastCheckin' => $lastCheckin,
        ]);
    }

    /**
     * Store a patrol check-in
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'map_location_id' => 'required|exists:map_locations,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $patrolLog = PatrolLog::create([
                'security_user_id' => auth()->id(),
                'map_location_id' => $validated['map_location_id'],
                'checked_in_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $location = MapLocation::find($validated['map_location_id']);

            // Log patrol check-in
            \Log::channel('patrol')->info('Patrol check-in recorded', [
                'patrol_log_id' => $patrolLog->id,
                'security_user_id' => auth()->id(),
                'security_user_name' => auth()->user()->first_name.' '.auth()->user()->last_name,
                'location_id' => $location->id,
                'location_name' => $location->name,
                'location_code' => $location->short_code,
                'has_notes' => ! empty($validated['notes']),
            ]);

            // Broadcast the patrol log creation to authorized administrators
            broadcast(new PatrolLogCreated($patrolLog));

            return redirect()
                ->route('security.patrol-scanner')
                ->with('success', "Successfully checked in at {$location->name} ({$location->short_code})");
        } catch (\Exception $e) {
            \Log::error('Patrol check-in failed: '.$e->getMessage());

            return back()->with('error', 'Failed to record patrol check-in. Please try again.');
        }
    }

    /**
     * Display patrol history for the current security guard
     */
    public function history(Request $request): View
    {
        $query = PatrolLog::where('security_user_id', auth()->id())
            ->with(['mapLocation.type'])
            ->latest('checked_in_at');

        // Filter by date range if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('checked_in_at', [
                $request->start_date.' 00:00:00',
                $request->end_date.' 23:59:59',
            ]);
        }

        $logs = $query->paginate(20);

        return view('security.patrol-history', [
            'pageTitle' => 'My Patrol History',
            'logs' => $logs,
        ]);
    }
}
