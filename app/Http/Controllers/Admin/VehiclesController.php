<?php

namespace App\Http\Controllers\Admin;

use App\Events\NotificationCreated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Vehicle;
use App\Services\PaymentBatchService;
use App\Services\StaticDataCacheService;
use App\Services\StickerGenerator;
use Illuminate\Support\Facades\DB;

class VehiclesController extends Controller
{
    protected $stickerGenerator;

    public function __construct(StickerGenerator $stickerGenerator)
    {
        $this->stickerGenerator = $stickerGenerator;
    }

    /**
     * Show the vehicles page.
     */
    public function index()
    {
        $vehicles = Vehicle::with(['user.student.college', 'type'])
            ->orderBy('created_at', 'desc')
            ->get();

        $vehicleTypes = StaticDataCacheService::getVehicleTypes();
        $colleges = StaticDataCacheService::getColleges();

        return view('admin.vehicles', [
            'pageTitle' => 'Vehicles Management',
            'vehicles' => $vehicles,
            'vehicleTypes' => $vehicleTypes,
            'colleges' => $colleges,
        ]);
    }

    /**
     * Get vehicles data for DataTables.
     */
    public function data()
    {
        $vehicles = Vehicle::with(['user', 'type'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($vehicles);
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(Vehicle $vehicle)
    {
        DB::transaction(function () use ($vehicle) {
            $userId = $vehicle->user_id;
            $plateNo = $vehicle->plate_no ?? $vehicle->color.' '.$vehicle->number;

            // Handle pending payments for this vehicle via service
            $batchSvc = app(PaymentBatchService::class);
            $editorName = auth()->user()->first_name.' '.auth()->user()->last_name;
            $batchSvc->removeVehicleFromBatch($vehicle->id, $editorName);

            // Broadcast vehicle deletion before deleting
            $vehicle->load(['user', 'type']);
            broadcast(new VehicleUpdated($vehicle, 'deleted', auth()->user()->first_name.' '.auth()->user()->last_name));

            // Soft delete the vehicle
            $vehicle->delete();

            // Create notification for the vehicle owner (skip if actor is the same user)
            if ((int) $userId !== (int) auth()->id()) {
                $notification = Notification::create([
                    'user_id' => $userId,
                    'type' => 'vehicle_deleted',
                    'title' => 'Vehicle Deleted',
                    'message' => "Your vehicle {$plateNo} has been deleted by an administrator.",
                    'data' => [
                        'vehicle_id' => $vehicle->id,
                        'action' => 'deleted',
                    ],
                ]);

                broadcast(new NotificationCreated($notification));
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully!',
        ]);
    }
}
