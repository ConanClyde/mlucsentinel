<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use App\Models\PatrolLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatrolHistoryController extends Controller
{
    /**
     * Display all patrol logs for admins
     */
    public function index(Request $request): View
    {
        // Get all patrol logs for client-side filtering and pagination
        $logs = PatrolLog::with(['securityUser', 'mapLocation.type'])
            ->latest('checked_in_at')
            ->paginate(1000); // Get up to 1000 logs for client-side handling

        // Get all security guards for filter dropdown
        $securityGuards = User::where('user_type', 'security')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        // Get all locations for filter dropdown
        $locations = MapLocation::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.patrol-history', [
            'pageTitle' => 'Patrol History',
            'logs' => $logs,
            'securityGuards' => $securityGuards,
            'locations' => $locations,
        ]);
    }

    /**
     * Export patrol logs to CSV
     */
    public function export(Request $request)
    {
        $query = PatrolLog::with(['securityUser', 'mapLocation'])
            ->latest('checked_in_at');

        // Apply filters
        if ($request->filled('security_user_id')) {
            $query->where('security_user_id', $request->security_user_id);
        }

        if ($request->filled('map_location_id')) {
            $query->where('map_location_id', $request->map_location_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('checked_in_at', [
                $request->start_date.' 00:00:00',
                $request->end_date.' 23:59:59',
            ]);
        }

        $logs = $query->get();

        $filename = 'patrol_logs_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Check-in Time',
                'Guard Name',
                'Location Name',
                'Location Code',
                'Notes',
                'Latitude',
                'Longitude',
            ]);

            // CSV rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->checked_in_at->format('Y-m-d H:i:s'),
                    $log->securityUser->name ?? 'N/A',
                    $log->mapLocation->name ?? 'N/A',
                    $log->mapLocation->short_code ?? 'N/A',
                    $log->notes ?? '',
                    $log->latitude ?? '',
                    $log->longitude ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
