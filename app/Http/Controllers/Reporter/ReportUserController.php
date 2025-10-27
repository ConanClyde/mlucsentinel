<?php

namespace App\Http\Controllers\Reporter;

use App\Events\ReportCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use App\Models\Vehicle;
use App\Models\ViolationType;
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
        if ($user->user_type === 'reporter' && $user->reporter) {
            $reporterType = $user->reporter->reporterType->name ?? '';

            if ($reporterType === 'SBO' && $vehicle->user->user_type !== 'student') {
                abort(403, 'SBO can only report student vehicles.');
            }
        }

        $violationTypes = ViolationType::all();

        return view('reporter.report-form', [
            'pageTitle' => 'Submit Report',
            'vehicle' => $vehicle,
            'violationTypes' => $violationTypes,
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
        if ($user->user_type === 'reporter' && $user->reporter) {
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
        $isSBO = $user->user_type === 'reporter' &&
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
        if ($user->user_type === 'reporter' && $user->reporter) {
            $reporterType = $user->reporter->reporterType->name ?? '';

            if ($reporterType === 'SBO' && $vehicle->user->user_type !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'SBO can only report student vehicles.',
                ], 403);
            }
        }

        // Handle evidence image upload
        $evidenceImagePath = null;
        if ($request->hasFile('evidence_image')) {
            $evidenceImagePath = $request->file('evidence_image')->store('evidence_images', 'public');
        }

        // Get auto-assigned admin based on violator type
        $assignment = Report::getAutoAssignedAdmin($vehicle->user->user_type);

        // Create the report
        $report = Report::create([
            'reported_by' => $user->id,
            'violator_vehicle_id' => $vehicle->id,
            'violator_sticker_number' => $vehicle->plate_no ? null : "{$vehicle->color}-{$vehicle->number}",
            'violation_type_id' => $request->violation_type_id,
            'description' => $request->description,
            'location' => $request->location,
            'assigned_to' => $assignment['assigned_to'] ?? null,
            'assigned_to_user_type' => $assignment['assigned_to_user_type'] ?? null,
            'status' => 'pending',
            'reported_at' => now(),
            'evidence_image' => $evidenceImagePath,
        ]);

        // Broadcast the report creation event for real-time updates
        broadcast(new ReportCreated($report))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully',
            'report_id' => $report->id,
            'assigned_to_role' => $vehicle->user->user_type === 'student' ? 'SAS Admin' : 'Chancellor Admin',
        ]);
    }
}
