<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Events\ReportStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportStatusRequest;
use App\Models\Report;
use App\Notifications\ReportStatusUpdatedNotification;
use App\Services\StaticDataCacheService;
use Illuminate\Http\Request;

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

        if ($user->user_type === UserType::GlobalAdministrator) {
            $canViewReports = true;
        } elseif ($user->user_type === UserType::Administrator && $user->administrator) {
            $adminRole = $user->administrator->adminRole->name ?? '';
            $canViewReports = in_array($adminRole, ['Chancellor', 'Security', 'SAS (Student Affairs & Services)']);
        }

        if (! $canViewReports) {
            abort(403, 'Unauthorized access. Only Chancellor, Security, and SAS administrators can view reports.');
        }

        // Determine admin role for filtering
        $adminRole = null;
        if ($user->user_type === UserType::GlobalAdministrator) {
            $adminRole = 'Global Administrator';
        } elseif ($user->user_type === UserType::Administrator && $user->administrator) {
            $adminRole = $user->administrator->adminRole->name ?? '';
        }

        // Fetch reports with relationships and filter based on admin role
        $reportsQuery = Report::select('reports.*') // Explicitly select all report columns including pin_x, pin_y
            ->with([
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
        $colleges = StaticDataCacheService::getColleges();

        // Get all violation types for filter dropdown
        $violationTypes = StaticDataCacheService::getViolationTypes();

        // Get map locations for the report detail modal
        $mapLocations = \App\Models\MapLocation::with('type')
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
                ];
            });

        return view('admin.reports', [
            'pageTitle' => 'Reports Management',
            'reports' => $reports,
            'adminRole' => $adminRole,
            'colleges' => $colleges,
            'violationTypes' => $violationTypes,
            'mapLocations' => $mapLocations,
        ]);
    }

    /**
     * Export reports with filters applied
     * Supports CSV and PDF formats
     */
    public function export(Request $request)
    {
        // Check if user has permission to view reports
        $user = auth()->user();
        $canViewReports = false;

        if ($user->user_type === UserType::GlobalAdministrator) {
            $canViewReports = true;
        } elseif ($user->user_type === UserType::Administrator && $user->administrator) {
            $adminRole = $user->administrator->adminRole->name ?? '';
            $canViewReports = in_array($adminRole, ['Chancellor', 'Security', 'SAS (Student Affairs & Services)']);
        }

        if (! $canViewReports) {
            abort(403, 'Unauthorized access.');
        }

        // Determine admin role for filtering
        $adminRole = null;
        if ($user->user_type === UserType::GlobalAdministrator) {
            $adminRole = 'Global Administrator';
        } elseif ($user->user_type === UserType::Administrator && $user->administrator) {
            $adminRole = $user->administrator->adminRole->name ?? '';
        }

        // Fetch all reports with relationships and filter based on admin role
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
            $reportsQuery->whereHas('violatorVehicle.user', function ($query) {
                $query->where('user_type', 'student');
            });
        } elseif (in_array($adminRole, ['Chancellor', 'Security'])) {
            $reportsQuery->whereHas('violatorVehicle.user', function ($query) {
                $query->where('user_type', '!=', 'student');
            });
        }

        // Order by date
        $reports = $reportsQuery
            ->orderBy('reported_at', 'desc')
            ->get();

        $filename = 'reports_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($reports) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Report ID',
                'Reported By',
                'Reporter Email',
                'Reporter Type',
                'Violator Name',
                'Violator Email',
                'Violator Type',
                'Vehicle Type',
                'Plate Number',
                'Sticker Number',
                'Violation Type',
                'Location',
                'Description',
                'Status',
                'Reported Date',
                'Status Updated',
                'Remarks',
            ]);

            // CSV rows
            foreach ($reports as $report) {
                fputcsv($file, [
                    $report->id,
                    $report->reportedBy ? $report->reportedBy->first_name.' '.$report->reportedBy->last_name : 'N/A',
                    $report->reportedBy->email ?? 'N/A',
                    $report->reportedBy->user_type ?? 'N/A',
                    $report->violatorVehicle && $report->violatorVehicle->user
                        ? $report->violatorVehicle->user->first_name.' '.$report->violatorVehicle->user->last_name
                        : 'N/A',
                    $report->violatorVehicle->user->email ?? 'N/A',
                    $report->violatorVehicle->user->user_type ?? 'N/A',
                    $report->violatorVehicle->type->name ?? 'N/A',
                    $report->violatorVehicle->plate_no ?? 'N/A',
                    $report->violator_sticker_number ?? 'N/A',
                    $report->violationType->name ?? 'N/A',
                    $report->location ?? 'N/A',
                    $report->description ?? 'N/A',
                    ucfirst($report->status ?? 'N/A'),
                    $report->reported_at ? $report->reported_at->format('Y-m-d H:i:s') : 'N/A',
                    $report->status_updated_at ? $report->status_updated_at->format('Y-m-d H:i:s') : 'N/A',
                    $report->remarks ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

        // Log report status change
        \Log::channel('reports')->info('Report status updated', [
            'report_id' => $report->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'violation_type' => $report->violationType->name ?? 'N/A',
            'location' => $report->location,
            'updated_by' => auth()->id(),
            'updated_by_name' => auth()->user()->first_name.' '.auth()->user()->last_name,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        // Audit log the status change
        \App\Services\AuditLogService::log('report_status_updated', $report, ['status' => $oldStatus], ['status' => $validated['status'], 'remarks' => $validated['remarks'] ?? null]);

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

        // Broadcast the status update for real-time updates
        broadcast(new ReportStatusUpdated($report))->toOthers();

        return response()->json([
            'message' => 'Status updated successfully',
            'status' => $report->status,
        ]);
    }
}
