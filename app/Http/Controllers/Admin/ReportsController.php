<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportStatusRequest;
use App\Models\Report;
use App\Notifications\ReportStatusUpdatedNotification;

class ReportsController extends Controller
{
    /**
     * Show the reports page.
     */
    public function index()
    {
        // Check if user has permission to view reports
        $user = auth()->user();
        $canViewReports = false;

        if ($user->user_type === 'global_administrator') {
            $canViewReports = true;
        } elseif ($user->user_type === 'administrator' && $user->administrator) {
            $adminRole = $user->administrator->adminRole->name ?? '';
            $canViewReports = in_array($adminRole, ['Chancellor', 'Security', 'SAS (Student Affairs & Services)']);
        }

        if (! $canViewReports) {
            abort(403, 'Unauthorized access. Only Chancellor, Security, and SAS administrators can view reports.');
        }

        // Determine admin role for filtering
        $adminRole = null;
        if ($user->user_type === 'global_administrator') {
            $adminRole = 'Global Administrator';
        } elseif ($user->user_type === 'administrator' && $user->administrator) {
            $adminRole = $user->administrator->adminRole->name ?? '';
        }

        // Fetch reports with relationships and filter based on admin role
        $reportsQuery = Report::with([
            'reportedBy:id,first_name,last_name,email,user_type',
            'violatorVehicle.user:id,first_name,last_name,email,user_type',
            'violatorVehicle.type:id,name',
            'violationType:id,name',
            'assignedTo:id,first_name,last_name',
            'updatedBy:id,first_name,last_name',
        ]);

        // Filter reports based on admin role
        if ($adminRole === 'SAS (Student Affairs & Services)') {
            // SAS Admin: Only show reports where violator is a STUDENT
            $reportsQuery->whereHas('violatorVehicle.user', function ($query) {
                $query->where('user_type', 'student');
            });
        } elseif (in_array($adminRole, ['Chancellor', 'Security'])) {
            // Chancellor & Security Admin: Only show reports where violator is NOT a student (staff, security, stakeholder)
            $reportsQuery->whereHas('violatorVehicle.user', function ($query) {
                $query->where('user_type', '!=', 'student');
            });
        }
        // Global administrators see ALL reports (no filter)

        // Order by status priority (pending first) then by date
        $reports = $reportsQuery
            ->orderByRaw("CASE WHEN status = 'pending' THEN 1 WHEN status = 'approved' THEN 2 WHEN status = 'rejected' THEN 3 ELSE 4 END")
            ->orderBy('reported_at', 'desc')
            ->paginate(50);

        // Get colleges for SAS filter
        $colleges = \App\Models\College::orderBy('name')->get();

        return view('admin.reports', [
            'pageTitle' => 'Reports Management',
            'reports' => $reports,
            'adminRole' => $adminRole,
            'colleges' => $colleges,
        ]);
    }

    /**
     * Update the status of a report.
     */
    public function updateStatus(UpdateReportStatusRequest $request, Report $report)
    {
        $validated = $request->validated();
        $oldStatus = $report->status;

        $report->update([
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
            'updated_by' => auth()->id(),
            'status_updated_at' => now(),
        ]);

        // Notify the reporter about the status change
        $reporter = $report->reportedBy;
        if ($reporter && $oldStatus !== $validated['status']) {
            $reporter->notify(new ReportStatusUpdatedNotification($report, $oldStatus, $validated['status']));
        }

        // Notify the violator when status is approved
        if ($validated['status'] === 'approved' && $oldStatus !== 'approved') {
            $violator = $report->violatorVehicle?->user;
            if ($violator) {
                $violator->notify(new \App\Notifications\ViolationApprovedNotification($report));
            }
        }

        return response()->json([
            'message' => 'Status updated successfully',
            'status' => $report->status,
        ]);
    }
}
