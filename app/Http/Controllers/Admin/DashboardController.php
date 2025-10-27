<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware is handled at route level
    }

    /**
     * Show the admin dashboard.
     */
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_users' => User::where('is_active', true)->count(),
            'total_vehicles' => Vehicle::where('is_active', true)->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'total_reports' => Report::count(),
        ];

        // Get report status distribution (for pie chart using new Enum)
        $reportsByStatus = Report::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Get user type distribution
        $userTypes = User::where('is_active', true)
            ->select('user_type', DB::raw('count(*) as count'))
            ->groupBy('user_type')
            ->get()
            ->pluck('count', 'user_type')
            ->toArray();

        // Get vehicle type distribution
        $vehicleTypes = VehicleType::withCount(['vehicles' => function ($query) {
            $query->where('is_active', true);
        }])->get()->pluck('vehicles_count', 'name')->toArray();

        // Get user registrations for the last 6 months
        $userRegistrations = User::where('created_at', '>=', now()->subMonths(6))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Get violations per day for the last 30 days
        $violationsPerDay = Report::where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Get top reporters (users who submitted most reports)
        $topReporters = User::withCount('reports')
            ->whereHas('reports')
            ->orderBy('reports_count', 'desc')
            ->limit(5)
            ->get();

        // Get top violators (users whose vehicles have most reports)
        $topViolators = User::withCount(['vehicles' => function ($query) {
            $query->whereHas('violatorReports');
        }])
            ->whereHas('vehicles.violatorReports')
            ->orderBy('vehicles_count', 'desc')
            ->limit(5)
            ->get();

        // Get top violation locations
        $topLocations = Report::select('location', DB::raw('count(*) as count'))
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->groupBy('location')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Get recent activity (last 10 activities)
        $recentActivity = $this->getRecentActivity();

        // Get recent reports for real-time updates
        $reports = Report::with([
            'reportedBy:id,first_name,last_name,email,user_type',
            'violatorVehicle.user:id,first_name,last_name,email,user_type',
            'violatorVehicle.type:id,name',
            'violationType:id,name',
            'assignedTo:id,first_name,last_name',
        ])->orderBy('reported_at', 'desc')->limit(50)->get();

        return view('admin.dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'stats' => $stats,
            'userTypes' => $userTypes,
            'vehicleTypes' => $vehicleTypes,
            'userRegistrations' => $userRegistrations,
            'violationsPerDay' => $violationsPerDay,
            'reportsByStatus' => $reportsByStatus,
            'topReporters' => $topReporters,
            'topViolators' => $topViolators,
            'topLocations' => $topLocations,
            'recentActivity' => $recentActivity,
            'reports' => $reports,
        ]);
    }

    /**
     * Get recent system activity
     */
    private function getRecentActivity()
    {
        $activities = collect();

        // Recent user registrations
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentUsers as $user) {
            $activities->push([
                'type' => 'user_registration',
                'message' => "New {$user->user_type} registered: {$user->first_name} {$user->last_name}",
                'time' => $user->created_at,
                'color' => 'green',
            ]);
        }

        // Recent reports
        $recentReports = Report::with(['reportedBy', 'violationType'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentReports as $report) {
            $activities->push([
                'type' => 'report_submitted',
                'message' => "New violation report submitted: {$report->violationType->name}",
                'time' => $report->created_at,
                'color' => 'yellow',
            ]);
        }

        // Recent vehicle registrations
        $recentVehicles = Vehicle::with(['user', 'type'])
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentVehicles as $vehicle) {
            $activities->push([
                'type' => 'vehicle_registered',
                'message' => "New {$vehicle->type->name} registered for {$vehicle->user->first_name} {$vehicle->user->last_name}",
                'time' => $vehicle->created_at,
                'color' => 'blue',
            ]);
        }

        return $activities->sortByDesc('time')->take(10)->values();
    }
}
