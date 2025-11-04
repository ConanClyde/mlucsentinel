<?php

namespace App\Http\Controllers\Reporter;

use App\Enums\UserType;
use App\Events\NotificationCreated;
use App\Events\ReportCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\StaticDataCacheService;
use Illuminate\Http\Request;

class ReportUserController extends Controller
{
    /**
     * Show the report user page with QR scan and manual entry options.
     */
    public function index()
    {
        return view('reporter.report-user', [
            'pageTitle' => 'Report User',
        ]);
    }

    /**
     * Show the report form for a specific vehicle.
     */
    public function showReportForm($vehicleId)
    {
        $user = auth()->user();
        $vehicle = Vehicle::with(['user', 'type'])->findOrFail($vehicleId);

        // Check if SBO is trying to report a non-student vehicle
        if ($user->user_type === UserType::Reporter && $user->reporter) {
            $reporterType = $user->reporter->reporterType->name ?? '';

            if ($reporterType === 'SBO' && $vehicle->user->user_type !== UserType::Student) {
                return redirect()
                    ->route('reporter.report-user')
                    ->with('error', 'SBO can only report student vehicles.');
            }
        }

        $violationTypes = StaticDataCacheService::getViolationTypes();
        $mapLocations = \App\Models\MapLocation::with('type')->active()->ordered()->get();

        return view('reporter.report-form', [
            'pageTitle' => 'Submit Report',
            'vehicle' => $vehicle,
            'violationTypes' => $violationTypes,
            'mapLocations' => $mapLocations,
        ]);
    }

    /**
     * Search for vehicle by sticker number and color or plate number.
     */
    public function searchVehicle(Request $request)
    {
        $user = auth()->user();
        $query = Vehicle::with(['user', 'type']);

        // Apply role-based filtering
        if ($user->user_type === UserType::Reporter && $user->reporter) {
            $reporterType = $user->reporter->reporterType->name ?? '';

            // SBO can only report students
            if ($reporterType === 'SBO') {
                $query->whereHas('user', function ($q) {
                    $q->where('user_type', 'student');
                });
            }
            // Other reporter types can report all users with vehicles
        }
        // Security can report all users with vehicles (no additional filtering)

        if ($request->has('plate_no') && $request->plate_no) {
            $vehicle = $query->where('plate_no', $request->plate_no)->first();
        } elseif ($request->has('sticker_number') && $request->has('sticker_color')) {
            $vehicle = $query->where('number', $request->sticker_number)
                ->where('color', $request->sticker_color)
                ->first();
        } else {
            return response()->json(['success' => false, 'message' => 'Please provide either plate number or sticker details']);
        }

        if ($vehicle) {
            return response()->json([
                'success' => true,
                'vehicle_id' => $vehicle->id,
                'redirect_url' => route('reporter.report-form', $vehicle->id),
            ]);
        }

        $user = auth()->user();
        $isSBO = $user->user_type === UserType::Reporter &&
                 $user->reporter &&
                 ($user->reporter->reporterType->name ?? '') === 'SBO';

        $message = $isSBO
            ? 'Student vehicle not found. SBO can only report student vehicles.'
            : 'Vehicle not found';

        return response()->json(['success' => false, 'message' => $message]);
    }

    /**
     * Store a new report.
     */
    public function store(StoreReportRequest $request)
    {
        $validated = $request->validated();

        $vehicle = Vehicle::with('user')->findOrFail($request->vehicle_id);
        $user = auth()->user();

        // Check if SBO is trying to report a non-student vehicle (extra validation)
        if ($user->user_type === UserType::Reporter && $user->reporter) {
            $reporterType = $user->reporter->reporterType->name ?? '';

            if ($reporterType === 'SBO' && $vehicle->user->user_type !== UserType::Student) {
                return response()->json([
                    'success' => false,
                    'message' => 'SBO can only report student vehicles.',
                ], 403);
            }
        }

        // Handle evidence image upload (store without optimization to avoid GD dependency)
        $evidenceImagePath = null;
        if ($request->hasFile('evidence_image')) {
            $file = $request->file('evidence_image');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $evidenceImagePath = $file->storeAs('evidence_images', $filename, 'public');
        }

        // Get auto-assigned admin based on violator type
        $assignment = Report::getAutoAssignedAdmin($vehicle->user->user_type->value);

        // Create the report
        $report = Report::create([
            'reported_by' => $user->id,
            'violator_vehicle_id' => $vehicle->id,
            'violator_sticker_number' => $vehicle->plate_no ? null : "{$vehicle->color}-{$vehicle->number}",
            'violation_type_id' => $request->violation_type_id,
            'description' => $request->description,
            'location' => $request->location,
            'pin_x' => $request->pin_x,
            'pin_y' => $request->pin_y,
            'assigned_to' => $assignment['assigned_to'] ?? null,
            'assigned_to_user_type' => $assignment['assigned_to_user_type'] ?? null,
            'status' => 'pending',
            'reported_at' => now(),
            'evidence_image' => $evidenceImagePath,
        ]);

        // Broadcast the report creation event for real-time updates
        broadcast(new ReportCreated($report))->toOthers();

        // Send notifications based on violator type
        $violatorName = "{$vehicle->user->first_name} {$vehicle->user->last_name}";
        $reporterName = "{$user->first_name} {$user->last_name}";
        $violationType = $report->violationType->name ?? 'Unknown';
        $violatorType = $vehicle->user->user_type->label();

        // Get Global Administrator (always notified)
        $globalAdmin = User::where('user_type', UserType::GlobalAdministrator)->first();

        if ($vehicle->user->user_type === UserType::Student) {
            // Get SAS Administrator for student violations
            $sasAdmin = User::where('user_type', UserType::Administrator)
                ->whereHas('administrator.adminRole', function ($query) {
                    $query->where('name', 'SAS (Student Affairs & Services)');
                })
                ->first();

            // Notify Global Admin
            if ($globalAdmin) {
                $notification = Notification::create([
                    'user_id' => $globalAdmin->id,
                    'type' => 'report_created',
                    'title' => 'New Student Violation Report',
                    'message' => "A new violation report has been submitted by {$reporterName} against student {$violatorName} for {$violationType}.",
                    'data' => [
                        'report_id' => $report->id,
                        'violator_name' => $violatorName,
                        'reporter_name' => $reporterName,
                        'violation_type' => $violationType,
                        'violator_type' => $violatorType,
                    ],
                    'is_read' => false,
                ]);
                broadcast(new NotificationCreated($notification));
            }

            // Notify SAS Admin
            if ($sasAdmin) {
                $notification = Notification::create([
                    'user_id' => $sasAdmin->id,
                    'type' => 'report_created',
                    'title' => 'New Student Violation Report',
                    'message' => "A new violation report has been submitted by {$reporterName} against student {$violatorName} for {$violationType}.",
                    'data' => [
                        'report_id' => $report->id,
                        'violator_name' => $violatorName,
                        'reporter_name' => $reporterName,
                        'violation_type' => $violationType,
                        'violator_type' => $violatorType,
                    ],
                    'is_read' => false,
                ]);
                broadcast(new NotificationCreated($notification));
            }
        } else {
            // Get Chancellor Administrator for non-student violations
            $chancellorAdmin = User::where('user_type', UserType::Administrator)
                ->whereHas('administrator.adminRole', function ($query) {
                    $query->where('name', 'Chancellor');
                })
                ->first();

            // Get Security Administrator for non-student violations
            $securityAdmin = User::where('user_type', UserType::Administrator)
                ->whereHas('administrator.adminRole', function ($query) {
                    $query->where('name', 'Security');
                })
                ->first();

            // Notify Global Admin
            if ($globalAdmin) {
                $notification = Notification::create([
                    'user_id' => $globalAdmin->id,
                    'type' => 'report_created',
                    'title' => 'New Violation Report',
                    'message' => "A new violation report has been submitted by {$reporterName} against {$violatorType} {$violatorName} for {$violationType}.",
                    'data' => [
                        'report_id' => $report->id,
                        'violator_name' => $violatorName,
                        'reporter_name' => $reporterName,
                        'violation_type' => $violationType,
                        'violator_type' => $violatorType,
                    ],
                    'is_read' => false,
                ]);
                broadcast(new NotificationCreated($notification));
            }

            // Notify Chancellor Admin
            if ($chancellorAdmin) {
                $notification = Notification::create([
                    'user_id' => $chancellorAdmin->id,
                    'type' => 'report_created',
                    'title' => 'New Violation Report',
                    'message' => "A new violation report has been submitted by {$reporterName} against {$violatorType} {$violatorName} for {$violationType}.",
                    'data' => [
                        'report_id' => $report->id,
                        'violator_name' => $violatorName,
                        'reporter_name' => $reporterName,
                        'violation_type' => $violationType,
                        'violator_type' => $violatorType,
                    ],
                    'is_read' => false,
                ]);
                broadcast(new NotificationCreated($notification));
            }

            // Notify Security Admin
            if ($securityAdmin) {
                $notification = Notification::create([
                    'user_id' => $securityAdmin->id,
                    'type' => 'report_created',
                    'title' => 'New Violation Report',
                    'message' => "A new violation report has been submitted by {$reporterName} against {$violatorType} {$violatorName} for {$violationType}.",
                    'data' => [
                        'report_id' => $report->id,
                        'violator_name' => $violatorName,
                        'reporter_name' => $reporterName,
                        'violation_type' => $violationType,
                        'violator_type' => $violatorType,
                    ],
                    'is_read' => false,
                ]);
                broadcast(new NotificationCreated($notification));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully',
            'report_id' => $report->id,
            'assigned_to_role' => $vehicle->user->user_type === UserType::Student ? 'SAS Admin' : 'Chancellor Admin',
        ]);
    }
}
