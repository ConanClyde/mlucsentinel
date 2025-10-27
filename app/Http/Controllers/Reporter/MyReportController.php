<?php

namespace App\Http\Controllers\Reporter;

use App\Http\Controllers\Controller;
use App\Models\Report;

class MyReportController extends Controller
{
    /**
     * Show the my reports page.
     */
    public function index()
    {
        $reports = Report::with([
            'violatorVehicle.user:id,first_name,last_name,email,user_type',
            'violatorVehicle.type:id,name',
            'violationType:id,name',
            'assignedTo:id,first_name,last_name',
        ])
            ->where('reported_by', auth()->id())
            ->orderBy('reported_at', 'desc')
            ->get();

        return view('reporter.my-reports', [
            'pageTitle' => 'My Reports',
            'reports' => $reports,
        ]);
    }
}
