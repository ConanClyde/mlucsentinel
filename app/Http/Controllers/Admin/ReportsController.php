<?php

namespace App\Http\Controllers\Admin;

use App\Events\NotificationCreated;
use App\Events\ReportStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportStatusRequest;
use App\Models\Notification;
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
        $user = auth()->user();

        // Check if user has permission to view/manage reports (consolidated privilege)
        if (! $user->hasPrivilege('manage_reports')) {
            abort(403, 'You do not have permission to view reports.');
        }
        $adminRole = $user->isGlobalAdministrator() ? 'Global Administrator' : ($user->administrator->adminRole->name ?? null);

        // Fetch reports with relationships and filter based on privileges
        $reportsQuery = Report::select('reports.*') // Explicitly select all report columns including pin_x, pin_y
            ->with([
                'reportedBy:id,first_name,last_name,email,user_type',
                'violatorVehicle.user:id,first_name,last_name,email,user_type',
                'violatorVehicle.type:id,name',
                'violationType:id,name',
                'assignedTo:id,first_name,last_name',
                'updatedBy:id,first_name,last_name',
            ]);

        // With consolidated privilege, show all reports to authorized admins

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
        $user = auth()->user();

        // Check if user has permission to view/manage reports (consolidated privilege)
        if (! $user->hasPrivilege('manage_reports')) {
            abort(403, 'You do not have permission to export reports.');
        }

        // Fetch all reports with relationships and filter based on privileges
        $reportsQuery = Report::with([
            'reportedBy:id,first_name,last_name,email,user_type',
            'violatorVehicle.user:id,first_name,last_name,email,user_type',
            'violatorVehicle.type:id,name',
            'violationType:id,name',
            'assignedTo:id,first_name,last_name',
            'updatedBy:id,first_name,last_name',
        ]);

        // With consolidated privilege, export all reports to authorized admins

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
        // Load relationships needed for notifications
        $report->loadMissing(['reportedBy', 'violationType', 'violatorVehicle.user', 'violatorVehicle.type']);

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
                // Load necessary relationships
                $report->loadMissing(['violationType', 'violatorVehicle.type']);

                // Send email notification
                $violator->notify(new \App\Notifications\ViolationApprovedNotification($report));

                // Create database notification for system notifications
                $violationTypeName = $report->violationType->name ?? 'Unknown';
                $notification = Notification::create([
                    'user_id' => $violator->id,
                    'type' => 'violation_approved',
                    'title' => 'Violation Report Approved',
                    'message' => "Your vehicle has been cited for {$violationTypeName}. Please take necessary action.",
                    'data' => [
                        'report_id' => $report->id,
                        'violation_type' => $violationTypeName,
                        'url' => route('home'),
                    ],
                ]);

                // Broadcast for real-time browser notifications
                broadcast(new NotificationCreated($notification));
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
