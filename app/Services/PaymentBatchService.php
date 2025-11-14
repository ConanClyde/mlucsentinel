<?php

namespace App\Services;

use App\Events\PaymentUpdated;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class PaymentBatchService
{
    protected function fee(): float
    {
        return \App\Models\Fee::getAmount('sticker_fee', 15.00);
    }

    public function addVehiclesToBatch(int $userId, array $vehicleIds, string $editorName): ?Payment
    {
        if (empty($vehicleIds)) {
            return null;
        }

        return DB::transaction(function () use ($userId, $vehicleIds, $editorName) {
            $representative = Payment::where('user_id', $userId)
                ->where('status', 'pending')
                ->where('is_representative', true)
                ->lockForUpdate()
                ->first();

            $fee = $this->fee();

            if ($representative && $representative->batch_id) {
                $batchId = $representative->batch_id;

                foreach ($vehicleIds as $vehicleId) {
                    Payment::create([
                        'user_id' => $userId,
                        'vehicle_id' => $vehicleId,
                        'type' => 'sticker_fee',
                        'status' => 'pending',
                        'amount' => $fee,
                        'reference' => 'STK-'.strtoupper(uniqid()),
                        'batch_id' => $batchId,
                        'vehicle_count' => 1,
                        'is_representative' => false,
                    ]);
                }

                $newVehicleCount = Payment::where('batch_id', $batchId)
                    ->where('status', 'pending')
                    ->count();

                $representative->update([
                    'vehicle_count' => $newVehicleCount,
                    'amount' => $newVehicleCount * $fee,
                ]);

                $representative->load(['user', 'vehicle.type', 'batchVehicles']);
                broadcast(new PaymentUpdated($representative, 'updated', $editorName));

                return $representative;
            }

            if ($representative && ! $representative->batch_id) {
                if (count($vehicleIds) === 0) {
                    $representative->load(['user', 'vehicle.type', 'batchVehicles']);
                    broadcast(new PaymentUpdated($representative, 'updated', $editorName));

                    return $representative;
                }

                $batchId = 'BATCH-'.strtoupper(uniqid());

                $representative->update([
                    'batch_id' => $batchId,
                    'vehicle_count' => 1,
                    'is_representative' => true,
                ]);

                foreach ($vehicleIds as $vehicleId) {
                    Payment::create([
                        'user_id' => $userId,
                        'vehicle_id' => $vehicleId,
                        'type' => 'sticker_fee',
                        'status' => 'pending',
                        'amount' => $fee,
                        'reference' => 'STK-'.strtoupper(uniqid()),
                        'batch_id' => $batchId,
                        'vehicle_count' => 1,
                        'is_representative' => false,
                    ]);
                }

                $newVehicleCount = Payment::where('batch_id', $batchId)
                    ->where('status', 'pending')
                    ->count();

                $representative->update([
                    'vehicle_count' => $newVehicleCount,
                    'amount' => $newVehicleCount * $fee,
                ]);

                $representative->load(['user', 'vehicle.type', 'batchVehicles']);
                broadcast(new PaymentUpdated($representative, 'updated', $editorName));

                return $representative;
            }

            if (count($vehicleIds) === 1) {
                $payment = Payment::create([
                    'user_id' => $userId,
                    'vehicle_id' => $vehicleIds[0],
                    'type' => 'sticker_fee',
                    'status' => 'pending',
                    'amount' => $fee,
                    'reference' => 'STK-'.strtoupper(uniqid()),
                    'batch_id' => null,
                    'vehicle_count' => 1,
                    'is_representative' => true,
                ]);

                $payment->load(['user', 'vehicle.type', 'batchVehicles']);
                broadcast(new PaymentUpdated($payment, 'created', $editorName));

                return $payment;
            }

            $batchId = 'BATCH-'.strtoupper(uniqid());
            $totalAmount = count($vehicleIds) * $fee;

            $payment = Payment::create([
                'user_id' => $userId,
                'vehicle_id' => $vehicleIds[0],
                'type' => 'sticker_fee',
                'status' => 'pending',
                'amount' => $totalAmount,
                'reference' => 'STK-'.strtoupper(uniqid()),
                'batch_id' => $batchId,
                'vehicle_count' => count($vehicleIds),
                'is_representative' => true,
            ]);

            for ($i = 1; $i < count($vehicleIds); $i++) {
                Payment::create([
                    'user_id' => $userId,
                    'vehicle_id' => $vehicleIds[$i],
                    'type' => 'sticker_fee',
                    'status' => 'pending',
                    'amount' => $fee,
                    'reference' => 'STK-'.strtoupper(uniqid()),
                    'batch_id' => $batchId,
                    'vehicle_count' => 1,
                    'is_representative' => false,
                ]);
            }

            $payment->load(['user', 'vehicle.type', 'batchVehicles']);
            broadcast(new PaymentUpdated($payment, 'created', $editorName));

            return $payment;
        });
    }

    public function removeVehicleFromBatch(int $vehicleId, string $editorName): void
    {
        DB::transaction(function () use ($vehicleId, $editorName) {
            $pendingPayment = Payment::where('vehicle_id', $vehicleId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $pendingPayment) {
                return;
            }

            $fee = $this->fee();

            if ($pendingPayment->batch_id) {
                $batchId = $pendingPayment->batch_id;
                $otherBatchPayments = Payment::where('batch_id', $batchId)
                    ->where('status', 'pending')
                    ->where('id', '!=', $pendingPayment->id)
                    ->get();

                $isRepresentative = $pendingPayment->is_representative;

                // If this is the representative, clear it first before deleting
                if ($isRepresentative) {
                    $pendingPayment->update(['is_representative' => false]);
                }

                $pendingPayment->delete();

                if ($otherBatchPayments->isEmpty()) {
                    broadcast(new PaymentUpdated($pendingPayment, 'deleted', $editorName));

                    return;
                }

                $newVehicleCount = $otherBatchPayments->count();
                $newAmount = $newVehicleCount * $fee;

                if ($isRepresentative) {
                    $newRepresentative = Payment::where('batch_id', $batchId)
                        ->where('status', 'pending')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->first();

                    if ($newRepresentative) {
                        $newRepresentative->update([
                            'vehicle_count' => $newVehicleCount,
                            'amount' => $newAmount,
                            'is_representative' => true,
                        ]);
                        $newRepresentative->load(['user', 'vehicle.type', 'batchVehicles']);
                        broadcast(new PaymentUpdated($newRepresentative, 'updated', $editorName));
                    }
                } else {
                    $representative = Payment::where('batch_id', $batchId)
                        ->where('status', 'pending')
                        ->where('is_representative', true)
                        ->lockForUpdate()
                        ->first();

                    if ($representative) {
                        $representative->update([
                            'vehicle_count' => $newVehicleCount,
                            'amount' => $newAmount,
                        ]);
                        $representative->load(['user', 'vehicle.type', 'batchVehicles']);
                        broadcast(new PaymentUpdated($representative, 'updated', $editorName));
                    }
                }

                return;
            }

            $pendingPayment->delete();
            broadcast(new PaymentUpdated($pendingPayment, 'deleted', $editorName));
        });
    }

    public function repriceBatch(string $batchId, string $editorName): ?Payment
    {
        return DB::transaction(function () use ($batchId, $editorName) {
            $payments = Payment::where('batch_id', $batchId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            if ($payments->isEmpty()) {
                return null;
            }

            $fee = $this->fee();
            $newVehicleCount = $payments->count();
            $newAmount = $newVehicleCount * $fee;

            $representative = $payments->firstWhere('is_representative', true);
            if (! $representative) {
                $representative = $payments->sortBy('id')->first();
            }

            $nonReps = $payments->where('id', '!=', $representative->id);
            foreach ($nonReps as $p) {
                if ($p->is_representative) {
                    $p->update(['is_representative' => false]);
                }
            }

            $representative->update([
                'vehicle_count' => $newVehicleCount,
                'amount' => $newAmount,
                'is_representative' => true,
            ]);

            $representative->load(['user', 'vehicle.type', 'batchVehicles']);
            broadcast(new PaymentUpdated($representative, 'updated', $editorName));

            return $representative;
        });
    }

    public function moveVehicleToUser(int $vehicleId, int $newUserId, string $editorName): void
    {
        DB::transaction(function () use ($vehicleId, $newUserId, $editorName) {
            $vehicle = Vehicle::lockForUpdate()->findOrFail($vehicleId);
            $oldUserId = $vehicle->user_id;
            if ($oldUserId === $newUserId) {
                return;
            }

            $this->removeVehicleFromBatch($vehicleId, $editorName);

            $vehicle->update(['user_id' => $newUserId]);

            $this->addVehiclesToBatch($newUserId, [$vehicleId], $editorName);
        });
    }
}
