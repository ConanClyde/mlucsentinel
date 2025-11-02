<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use App\Models\PatrolLog;
use App\Models\Payment;
use App\Models\Report;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\ViolationType;
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
     * Show the admin dashboard with comprehensive analytics.
     */
    public function index()
    {
        // Get dashboard statistics (including revenue)
        $stats = [
            'total_users' => User::where('is_active', true)->count(),
            'total_vehicles' => Vehicle::where('is_active', true)->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'total_reports' => Report::count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'total_payments' => Payment::whereIn('status', ['pending', 'paid'])->count(),
            'paid_payments' => Payment::where('status', 'paid')->count(),
            'pending_payments' => Payment::where('status', 'pending')->count(),
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
            ->mapWithKeys(function ($item) {
                return [$item->user_type->value => $item->count];
            })
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

        // Get top violators this month (NEW WIDGET)
        $topViolatorsThisMonth = User::withCount(['vehicles' => function ($query) {
            $query->whereHas('violatorReports', function ($q) {
                $q->where('created_at', '>=', now()->startOfMonth());
            });
        }])
            ->whereHas('vehicles.violatorReports', function ($q) {
                $q->where('created_at', '>=', now()->startOfMonth());
            })
            ->having('vehicles_count', '>', 0)
            ->orderBy('vehicles_count', 'desc')
            ->limit(10)
            ->get();

        // Get top violation locations
        $topLocations = Report::select('location', DB::raw('count(*) as count'))
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->groupBy('location')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Get all reports with their exact pin coordinates for heatmap dots
        $reportsForHeatmap = Report::select('id', 'location', 'pin_x', 'pin_y', 'reported_at', 'violation_type_id')
            ->with('violationType:id,name')
            ->whereNotNull('pin_x')
            ->whereNotNull('pin_y')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'location' => $report->location,
                    'x' => floatval($report->pin_x),
                    'y' => floatval($report->pin_y),
                    'violation_type' => $report->violationType->name ?? 'Unknown',
                    'reported_at' => $report->reported_at->format('M d, Y'),
                ];
            });

        // Get map locations for display on heatmap (view-only)
        $mapLocations = MapLocation::with('type')
            ->where('is_active', true)
            ->whereNotNull('vertices')
            ->orderBy('display_order')
            ->get()
            ->map(function ($location) {
                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'short_code' => $location->short_code,
                    'type' => [
                        'id' => $location->type->id,
                        'name' => $location->type->name,
                    ],
                    'color' => $location->color,
                    'vertices' => $location->vertices,
                    'center_x' => $location->center_x,
                    'center_y' => $location->center_y,
                    'description' => $location->description,
                ];
            });

        // Get recent activity (last 10 activities)
        $recentActivity = $this->getRecentActivity();

        // Get recent reports for real-time updates (full list used by charts/realtime)
        $reports = Report::with([
            'reportedBy:id,first_name,last_name,email,user_type',
            'violatorVehicle.user:id,first_name,last_name,email,user_type',
            'violatorVehicle.type:id,name',
            'violationType:id,name',
            'assignedTo:id,first_name,last_name',
        ])->orderBy('reported_at', 'desc')->limit(50)->get();

        // Recent Reports (compact list for dashboard widget)
        $recentReports = Report::with(['reportedBy', 'violationType'])
            ->orderBy('reported_at', 'desc')
            ->limit(10)
            ->get();

        // Recent Users (latest registrations)
        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get payment statistics
        $paymentsByStatus = Payment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Get monthly revenue for the last 12 months
        $monthlyRevenue = Payment::where('created_at', '>=', now()->subMonths(12))
            ->where('status', 'paid')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('sum(amount) as total'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Get violations by type
        $violationsByType = ViolationType::withCount('reports')
            ->get()
            ->pluck('reports_count', 'name')
            ->toArray();

        // Get patrol statistics (last 24 hours)
        $patrolStats = $this->getPatrolStats();

        // Get sticker issuance trends (last 12 months) - Marketing Admin only
        $stickerIssuanceTrends = Payment::where('created_at', '>=', now()->subMonths(12))
            ->whereIn('status', ['pending', 'paid'])
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as total'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        // Get patrol coverage data (Security/Global Admin only)
        $patrolCoverageData = $this->getPatrolCoverageData();

        return view('admin.dashboard', [
            'pageTitle' => 'Dashboard & Analytics',
            'stats' => $stats,
            'userTypes' => $userTypes,
            'vehicleTypes' => $vehicleTypes,
            'userRegistrations' => $userRegistrations,
            'violationsPerDay' => $violationsPerDay,
            'reportsByStatus' => $reportsByStatus,
            'topReporters' => $topReporters,
            'topViolators' => $topViolators,
            'topLocations' => $topLocations,
            'reportsForHeatmap' => $reportsForHeatmap,
            'mapLocations' => $mapLocations,
            'recentActivity' => $recentActivity,
            'reports' => $reports,
            'recentReports' => $recentReports,
            'recentUsers' => $recentUsers,
            'paymentsByStatus' => $paymentsByStatus,
            'monthlyRevenue' => $monthlyRevenue,
            'violationsByType' => $violationsByType,
            'patrolStats' => $patrolStats,
            'topViolatorsThisMonth' => $topViolatorsThisMonth,
            'stickerIssuanceTrends' => $stickerIssuanceTrends,
            'patrolCoverageData' => $patrolCoverageData,
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
                'message' => "New {$user->user_type->label()} registered: {$user->first_name} {$user->last_name}",
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

    /**
     * Get patrol statistics (last 24 hours)
     */
    private function getPatrolStats(): array
    {
        $query = PatrolLog::where('checked_in_at', '>=', now()->subHours(24));

        $totalCheckins = $query->count();
        $uniqueGuards = $query->distinct('security_user_id')->count('security_user_id');
        $uniqueLocations = $query->distinct('map_location_id')->count('map_location_id');

        // Most visited location
        $mostVisitedLocation = PatrolLog::selectRaw('map_location_id, COUNT(*) as visit_count')
            ->where('checked_in_at', '>=', now()->subHours(24))
            ->groupBy('map_location_id')
            ->orderByDesc('visit_count')
            ->with('mapLocation')
            ->first();

        return [
            'total_checkins' => $totalCheckins,
            'unique_guards' => $uniqueGuards,
            'unique_locations' => $uniqueLocations,
            'most_visited_location' => $mostVisitedLocation,
        ];
    }

    /**
     * Get patrol coverage data for heatmap
     */
    private function getPatrolCoverageData(): array
    {
        // Get all patrol check-ins with their map locations from last 7 days
        $patrols = PatrolLog::with(['mapLocation', 'securityUser'])
            ->where('checked_in_at', '>=', now()->subDays(7))
            ->get();

        // Group by location to show frequency
        $locationCoverage = [];
        foreach ($patrols as $patrol) {
            if ($patrol->mapLocation) {
                $locationId = $patrol->mapLocation->id;
                if (! isset($locationCoverage[$locationId])) {
                    $locationCoverage[$locationId] = [
                        'location' => $patrol->mapLocation,
                        'checkins' => 0,
                        'guards' => [],
                    ];
                }
                $locationCoverage[$locationId]['checkins']++;
                $locationCoverage[$locationId]['guards'][$patrol->security_user_id] = true;
            }
        }

        // Calculate coverage percentage
        $totalLocations = MapLocation::where('is_active', true)->count();
        $coveredLocations = count($locationCoverage);
        $coveragePercentage = $totalLocations > 0 ? round(($coveredLocations / $totalLocations) * 100, 1) : 0;

        return [
            'locations' => array_values($locationCoverage),
            'coverage_percentage' => $coveragePercentage,
            'total_patrols' => $patrols->count(),
            'total_locations' => $totalLocations,
            'covered_locations' => $coveredLocations,
        ];
    }
}
