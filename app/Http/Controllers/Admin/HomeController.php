<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Vehicle;

class HomeController extends Controller
{
    /**
     * Show the admin home page.
     */
    public function index()
    {
        // Get recent activity (last 10 activities)
        $recentActivity = $this->getRecentActivity();

        // Get recent users count (registered today)
        $recentUsersCount = User::whereDate('created_at', today())->count();

        // Get recent reports for real-time updates
        $reports = Report::with([
            'reportedBy:id,first_name,last_name,email,user_type',
            'violatorVehicle.user:id,first_name,last_name,email,user_type',
            'violatorVehicle.type:id,name',
            'violationType:id,name',
            'assignedTo:id,first_name,last_name',
        ])->orderBy('reported_at', 'desc')->limit(50)->get();

        return view('admin.home', [
            'pageTitle' => 'Admin Home',
            'recentActivity' => $recentActivity,
            'recentUsersCount' => $recentUsersCount,
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
