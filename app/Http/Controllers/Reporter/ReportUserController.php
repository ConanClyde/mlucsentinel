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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportUserController extends Controller
{
    /**
     * Show the report user page with QR scan and manual entry options.
     */
    public function index(): View
    {
        return view('reporter.report-user', [
            'pageTitle' => 'Report User',
        ]);
    }

    /**
     * Show the report form for a specific vehicle.
     */
    public function showReportForm($vehicleId): View|RedirectResponse
    {
        $user = auth()->user();
        $vehicle = Vehicle::with(['user', 'type'])->findOrFail($vehicleId);

        // Check if reporter has permission to report this user type
        if (! $user->canReportUser($vehicle->user)) {
            return redirect()
                ->route('reporter.report-user')
                ->with('error', 'You are not authorized to report this user type.');
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
    public function searchVehicle(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Vehicle::with(['user', 'type']);

        // Apply role-based filtering based on reporter role permissions
        if ($user->reporter && $user->reporter->reporterRole) {
            $allowedUserTypes = $user->reporter->reporterRole->getAllowedUserTypes();

            // Filter vehicles to only show those the reporter can report
            if (! empty($allowedUserTypes)) {
                $query->whereHas('user', function ($q) use ($allowedUserTypes) {
                    $q->whereIn('user_type', $allowedUserTypes);
                });
            }
        }
        // If no reporter role, allow all (for security or backward compatibility)

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
            // Double-check authorization before returning vehicle
            if (! $user->canReportUser($vehicle->user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to report this user type.',
                ]);
            }

            return response()->json([
                'success' => true,
                'vehicle_id' => $vehicle->id,
                'redirect_url' => route('reporter.report-form', $vehicle->id),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Vehicle not found']);
    }

    /**
     * Store a new report.
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $vehicle = Vehicle::with('user')->findOrFail($request->vehicle_id);
        $user = auth()->user();

        // Check if reporter has permission to report this user (extra validation)
        if (! $user->canReportUser($vehicle->user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to report this user type.',
            ], 403);
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

        // Send notifications based on admin role report targets (configured under Admin Roles)
        $violatorName = "{$vehicle->user->first_name} {$vehicle->user->last_name}";
        $reporterName = "{$user->first_name} {$user->last_name}";
        $violationType = $report->violationType->name ?? 'Unknown';
        $violatorType = $vehicle->user->user_type->label();
        $violatorTypeValue = $vehicle->user->user_type->value; // e.g., 'student', 'staff'

        // Collect recipients
        $recipients = collect();

        // Always include Global Administrator(s)
        $globalAdmins = User::where('user_type', UserType::GlobalAdministrator)->get();
        $recipients = $recipients->merge($globalAdmins);

        // Include Administrators whose role wants notifications for this violator user type
        $adminUsers = User::where('user_type', UserType::Administrator)
            ->whereHas('administrator.adminRole', function ($q) {
                $q->where('is_active', true);
            })->with(['administrator.adminRole'])->get();

        foreach ($adminUsers as $admin) {
            $role = $admin->administrator->adminRole ?? null;
            if ($role && $role->wantsReportFor($violatorTypeValue)) {
                $recipients->push($admin);
            }
        }

        // De-duplicate recipients by user ID
        $recipients = $recipients->unique('id');

        // Create and broadcast notifications
        foreach ($recipients as $recipient) {
            $notification = Notification::create([
                'user_id' => $recipient->id,
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

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully',
            'report_id' => $report->id,
        ]);
    }
}
