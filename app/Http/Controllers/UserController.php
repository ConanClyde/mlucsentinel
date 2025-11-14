<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\StickerRequest;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Show the user dashboard
     */
    public function home(): View
    {
        $this->checkVehicleUserAccess();
        $user = Auth::user();

        // Get user's vehicles
        $vehicles = $this->getUserVehicles();

        // Calculate statistics
        $vehicleCount = $vehicles->count();
        $activeStickerCount = $vehicles->whereNotNull('sticker')->count();

        // Get violation count
        $violationCount = Report::whereHas('violatorVehicle', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        // Get pending request count
        $pendingRequestCount = StickerRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Get recent activity (last 10 activities)
        $recentActivity = collect();

        // Add recent violation reports
        $recentViolations = Report::whereHas('violatorVehicle', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['violatorVehicle.vehicleType'])->latest()->take(5)->get();

        foreach ($recentViolations as $violation) {
            $recentActivity->push((object) [
                'description' => "Violation report #{$violation->id} for {$violation->violatorVehicle->vehicleType->name}",
                'created_at' => $violation->created_at,
            ]);
        }

        // Add recent sticker requests
        $recentRequests = StickerRequest::where('user_id', $user->id)
            ->latest()->take(5)->get();

        foreach ($recentRequests as $request) {
            $recentActivity->push((object) [
                'description' => "Sticker request #{$request->id} submitted for {$request->vehicle->vehicleType->name}",
                'created_at' => $request->created_at,
            ]);
        }

        // Sort by date and take latest 10
        $recentActivity = $recentActivity->sortByDesc('created_at')->take(10);

        return view('user.home', compact(
            'vehicleCount',
            'activeStickerCount',
            'violationCount',
            'pendingRequestCount',
            'recentActivity'
        ));
    }

    /**
     * Show user's vehicles
     */
    public function vehicles(): View
    {
        $this->checkVehicleUserAccess();

        $vehicles = Vehicle::where('user_id', Auth::id())
            ->with(['type'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'active' => $vehicles->where('is_active', true)->count(),
            'inactive' => $vehicles->where('is_active', false)->count(),
            'total' => $vehicles->count(),
        ];

        return view('reporter.my-vehicles', [
            'pageTitle' => 'My Vehicles',
            'vehicles' => $vehicles,
            'stats' => $stats,
        ]);
    }

    /**
     * Show user's violation report history
     */
    public function reports(Request $request): View
    {
        $this->checkVehicleUserAccess();
        $user = Auth::user();
        $userVehicles = Vehicle::where('user_id', $user->id)->get();

        $query = Report::whereHas('violatorVehicle', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['violatorVehicle.vehicleType', 'violationType', 'reportedBy']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vehicle')) {
            $query->where('violator_vehicle_id', $request->vehicle);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $reports = $query->latest()->paginate(15);

        return view('user.reports', compact('reports', 'userVehicles'));
    }

    /**
     * Get report details (AJAX)
     */
    public function getReportDetails($id): JsonResponse
    {
        $user = Auth::user();

        $report = Report::whereHas('violatorVehicle', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['violatorVehicle.vehicleType', 'violationType', 'reportedBy'])->findOrFail($id);

        return response()->json([
            'id' => $report->id,
            'status' => ucfirst($report->status),
            'vehicle' => [
                'type' => $report->violatorVehicle->vehicleType->name,
                'plate_no' => $report->violatorVehicle->plate_no,
            ],
            'violation_type' => $report->violationType->name,
            'reporter' => [
                'name' => $report->reportedBy->first_name.' '.$report->reportedBy->last_name,
            ],
            'description' => $report->description,
            'location' => $report->location,
            'date_time' => $report->created_at->format('M j, Y g:i A'),
            'evidence_photos' => $report->evidence_photos ? json_decode($report->evidence_photos) : [],
        ]);
    }

    /**
     * Show user's sticker requests
     */
    public function requests(Request $request): View
    {
        $this->checkVehicleUserAccess();
        $user = Auth::user();
        $userVehicles = Vehicle::where('user_id', $user->id)->with(['vehicleType'])->get();

        $query = StickerRequest::where('user_id', $user->id)
            ->with(['vehicle.vehicleType']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vehicle')) {
            $query->where('vehicle_id', $request->vehicle);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $requests = $query->latest()->paginate(15);

        return view('user.requests', compact('requests', 'userVehicles'));
    }

    /**
     * Show a specific sticker request
     */
    public function showRequest($id): JsonResponse
    {
        $this->checkVehicleUserAccess();
        $user = Auth::user();

        $request = StickerRequest::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['vehicle.vehicleType', 'processedBy'])
            ->firstOrFail();

        return response()->json($request);
    }

    /**
     * Store a new sticker request
     */
    public function storeRequest(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->checkVehicleUserAccess();
        $user = Auth::user();

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'reason' => 'required|string|max:1000',
            'additional_info' => 'nullable|string|max:1000',
            'terms_accepted' => 'required|accepted',
        ]);

        // Verify the vehicle belongs to the user
        $vehicle = Vehicle::where('id', $request->vehicle_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if there's already a pending request for this vehicle
        $existingRequest = StickerRequest::where('user_id', $user->id)
            ->where('vehicle_id', $request->vehicle_id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending request for this vehicle.',
                    'errors' => ['vehicle_id' => ['You already have a pending request for this vehicle.']],
                ], 422);
            }

            return back()->withErrors(['vehicle_id' => 'You already have a pending request for this vehicle.']);
        }

        // Create the request
        $stickerRequest = StickerRequest::create([
            'user_id' => $user->id,
            'vehicle_id' => $request->vehicle_id,
            'request_type' => 'new',
            'reason' => $request->reason,
            'additional_info' => $request->additional_info,
            'status' => 'pending',
        ]);

        // Load relationships for notifications
        $stickerRequest->load(['user', 'vehicle.vehicleType']);

        // Notify admins with sticker management privileges
        $admins = \App\Models\User::whereHas('administrator.adminRole.privileges', function ($query) {
            $query->where('name', 'view_stickers');
        })->get();

        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewStickerRequestNotification($stickerRequest));
        }

        // Broadcast real-time notification
        broadcast(new \App\Events\StickerRequestCreated($stickerRequest))->toOthers();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Sticker request submitted successfully!',
                'redirect' => route('user.requests'),
            ]);
        }

        // Handle normal form submission
        return redirect()->route('user.requests')->with('success', 'Sticker request submitted successfully!');
    }

    /**
     * Get request details (AJAX)
     */
    public function getRequestDetails($id): JsonResponse
    {
        $user = Auth::user();

        $request = StickerRequest::where('user_id', $user->id)
            ->with(['vehicle.vehicleType'])
            ->findOrFail($id);

        return response()->json([
            'id' => $request->id,
            'status' => $request->status,
            'vehicle' => [
                'vehicle_type' => [
                    'name' => $request->vehicle->vehicleType->name,
                ],
                'plate_no' => $request->vehicle->plate_no,
                'color' => $request->vehicle->color,
                'sticker' => $request->vehicle->sticker,
            ],
            'request_type' => ucfirst($request->request_type),
            'reason' => $request->reason,
            'additional_info' => $request->additional_info,
            'created_at' => $request->created_at->toIso8601String(),
            'processed_at' => $request->processed_at ? $request->processed_at->toIso8601String() : null,
            'admin_notes' => $request->admin_notes,
        ]);
    }

    /**
     * Cancel a pending request
     */
    public function cancelRequest($id): JsonResponse
    {
        $user = Auth::user();

        $request = StickerRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $request->update([
            'status' => 'cancelled',
            'processed_at' => now(),
            'admin_notes' => 'Cancelled by user',
        ]);

        return response()->json(['success' => true, 'message' => 'Request cancelled successfully.']);
    }

    /**
     * Get user's vehicles based on user type
     */
    private function getUserVehicles(): \Illuminate\Database\Eloquent\Collection
    {
        $user = Auth::user();

        // All vehicle users use the same vehicles table with user_id
        return Vehicle::where('user_id', $user->id)->get();
    }

    /**
     * Check if current user has access to vehicle user features
     */
    private function checkVehicleUserAccess(): void
    {
        $user = Auth::user();
        if (! in_array($user->user_type, [\App\Enums\UserType::Student, \App\Enums\UserType::Staff, \App\Enums\UserType::Stakeholder])) {
            abort(403, 'Access denied. This area is for vehicle users only.');
        }
    }
}
