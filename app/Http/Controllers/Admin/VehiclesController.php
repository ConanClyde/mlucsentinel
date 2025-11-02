<?php

namespace App\Http\Controllers\Admin;

use App\Events\NotificationCreated;
use App\Events\PaymentUpdated;
use App\Events\VehicleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Vehicle;
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

            // Handle pending payments for this vehicle
            $pendingPayment = Payment::where('vehicle_id', $vehicle->id)
                ->where('status', 'pending')
                ->first();

            if ($pendingPayment) {
                // Check if this payment is part of a batch
                if ($pendingPayment->batch_id) {
                    // Get all payments in this batch (including the current one)
                    $allBatchPayments = Payment::where('batch_id', $pendingPayment->batch_id)
                        ->where('status', 'pending')
                        ->get();

                    // Get other payments in the batch (excluding current)
                    $otherBatchPayments = $allBatchPayments->where('id', '!=', $pendingPayment->id);

                    if ($otherBatchPayments->isEmpty()) {
                        // This is the last vehicle in the batch - delete the entire payment
                        $pendingPayment->delete();
                        broadcast(new PaymentUpdated($pendingPayment, 'deleted', auth()->user()->first_name.' '.auth()->user()->last_name));
                    } else {
                        // There are other vehicles in the batch
                        $newVehicleCount = $otherBatchPayments->count();
                        $newAmount = $newVehicleCount * 15.00;

                        // Check if we're deleting the representative payment (the one with vehicle_count > 1)
                        $isRepresentative = $pendingPayment->vehicle_count > 1;

                        if ($isRepresentative) {
                            // Transfer representative role to another payment in the batch
                            $newRepresentative = $otherBatchPayments->first();
                            $newRepresentative->update([
                                'vehicle_count' => $newVehicleCount,
                                'amount' => $newAmount,
                            ]);

                            // Update all other payments in the batch
                            Payment::where('batch_id', $pendingPayment->batch_id)
                                ->where('status', 'pending')
                                ->where('id', '!=', $newRepresentative->id)
                                ->update([
                                    'vehicle_count' => 1,
                                    'amount' => 15.00,
                                ]);

                            // Delete the old representative payment
                            $pendingPayment->delete();

                            // Broadcast update with the new representative
                            $newRepresentative->load(['user', 'vehicle.type', 'batchVehicles']);
                            broadcast(new PaymentUpdated($newRepresentative, 'updated', auth()->user()->first_name.' '.auth()->user()->last_name));
                        } else {
                            // Deleting a non-representative payment, just delete it and update the representative
                            $pendingPayment->delete();

                            // Update the representative payment
                            Payment::where('batch_id', $pendingPayment->batch_id)
                                ->where('status', 'pending')
                                ->where('vehicle_count', '>', 1)
                                ->update([
                                    'vehicle_count' => $newVehicleCount,
                                    'amount' => $newAmount,
                                ]);

                            // Broadcast update
                            $representative = Payment::where('batch_id', $pendingPayment->batch_id)
                                ->where('status', 'pending')
                                ->where('vehicle_count', '>', 1)
                                ->with(['user', 'vehicle.type', 'batchVehicles'])
                                ->first();

                            if ($representative) {
                                broadcast(new PaymentUpdated($representative, 'updated', auth()->user()->first_name.' '.auth()->user()->last_name));
                            }
                        }
                    }
                } else {
                    // Single vehicle payment - delete the entire payment
                    $pendingPayment->delete();

                    // Broadcast payment deletion
                    broadcast(new PaymentUpdated($pendingPayment, 'deleted', auth()->user()->first_name.' '.auth()->user()->last_name));
                }
            }

            // Broadcast vehicle deletion before deleting
            $vehicle->load(['user', 'type']);
            broadcast(new VehicleUpdated($vehicle, 'deleted', auth()->user()->first_name.' '.auth()->user()->last_name));

            // Soft delete the vehicle
            $vehicle->delete();

            // Create notification for the vehicle owner
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => 'vehicle_deleted',
                'title' => 'Vehicle Deleted',
                'message' => "Your vehicle {$plateNo} has been deleted by an administrator.",
                'data' => json_encode([
                    'vehicle_id' => $vehicle->id,
                    'action' => 'deleted',
                ]),
            ]);

            broadcast(new NotificationCreated($notification));
        });

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully!',
        ]);
    }
}
