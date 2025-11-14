<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatrolLog;
use App\Models\Payment;
use App\Models\Report;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function overview()
    {
        $stats = Cache::remember('metrics.overview.stats', 60, function () {
            return [
                'total_users' => User::where('is_active', true)->count(),
                'total_vehicles' => Vehicle::where('is_active', true)->count(),
                'pending_reports' => Report::where('status', 'pending')->count(),
                'total_reports' => Report::count(),
                'total_revenue' => (float) Payment::where('status', 'paid')->sum('amount'),
                'total_payments' => Payment::whereIn('status', ['pending', 'paid'])->count(),
                'paid_payments' => Payment::where('status', 'paid')->count(),
                'pending_payments' => Payment::where('status', 'pending')->count(),
            ];
        });

        $paymentsByStatus = Cache::remember('metrics.overview.paymentsByStatus', 60, function () {
            return Payment::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();
        });

        $reportsByStatus = Cache::remember('metrics.overview.reportsByStatus', 60, function () {
            return Report::select(DB::raw('status'), DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();
        });

        $userTypes = Cache::remember('metrics.overview.userTypes', 60, function () {
            return User::where('is_active', true)
                ->select('user_type', DB::raw('count(*) as count'))
                ->groupBy('user_type')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->user_type->value => $item->count];
                })
                ->toArray();
        });

        $vehicleTypes = Cache::remember('metrics.overview.vehicleTypes', 60, function () {
            return VehicleType::withCount(['vehicles' => function ($query) {
                $query->where('is_active', true);
            }])->get()->pluck('vehicles_count', 'name')->toArray();
        });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'paymentsByStatus' => $paymentsByStatus,
            'reportsByStatus' => $reportsByStatus,
            'userTypes' => $userTypes,
            'vehicleTypes' => $vehicleTypes,
        ]);
    }

    public function violationsPerDay()
    {
        $data = Cache::remember('metrics.violationsPerDay', 60, function () {
            return Report::where('created_at', '>=', now()->subDays(30))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function paymentsMonthly()
    {
        $data = Cache::remember('metrics.paymentsMonthly', 60, function () {
            return Payment::where('created_at', '>=', now()->subMonths(12))
                ->where('status', 'paid')
                ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('sum(amount) as total'))
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function patrolStats24h()
    {
        $data = Cache::remember('metrics.patrol.24h', 30, function () {
            $query = PatrolLog::where('checked_in_at', '>=', now()->subHours(24));

            $totalCheckins = $query->count();
            $uniqueGuards = $query->distinct('security_user_id')->count('security_user_id');
            $uniqueLocations = $query->distinct('map_location_id')->count('map_location_id');

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
        });

        return response()->json(['success' => true, 'data' => $data]);
    }
}
