<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizePaymentBatches extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payments:normalize-batches
                            {--status=all : Scope of records to consider for recalculations (pending|all)}
                            {--batch_id= : Only normalize a specific batch_id}';

    /**
     * The console command description.
     */
    protected $description = 'Ensure exactly one representative per batch and fix vehicle_count/amount for pending batches';

    public function handle(): int
    {
        $scope = strtolower($this->option('status') ?? 'all');
        if (! in_array($scope, ['pending', 'all'], true)) {
            $this->error("Invalid --status option. Use 'pending' or 'all'.");

            return self::INVALID;
        }

        $onlyBatchId = $this->option('batch_id');
        $fee = \App\Models\Fee::getAmount('sticker_fee', 15.00);

        $this->info("Normalizing payment batches (scope: {$scope})...");

        // Build base query for distinct batch IDs
        $base = Payment::query()->whereNotNull('batch_id');
        if ($onlyBatchId) {
            $base->where('batch_id', $onlyBatchId);
        }

        $batchIds = $base->select('batch_id')->distinct()->pluck('batch_id');
        if ($batchIds->isEmpty()) {
            $this->info('No batches found to normalize.');

            return self::SUCCESS;
        }

        $fixedBatches = 0;
        $reprAdjusted = 0;
        $childrenDemoted = 0;

        foreach ($batchIds as $batchId) {
            DB::transaction(function () use ($batchId, $scope, $fee, &$fixedBatches, &$reprAdjusted, &$childrenDemoted) {
                // Load all payments for this batch across all statuses to ensure a single representative overall
                $payments = Payment::where('batch_id', $batchId)->get();
                if ($payments->isEmpty()) {
                    return;
                }

                // Choose a representative: prefer an existing representative, otherwise earliest by id
                $rep = $payments->firstWhere('is_representative', true) ?: $payments->sortBy('id')->first();

                // If multiple marked as representative, demote the rest
                $repId = $rep->id;
                $otherIds = $payments->where('id', '!=', $repId)->pluck('id');

                if ($otherIds->isNotEmpty()) {
                    // Demote all others
                    $childrenDemoted += Payment::whereIn('id', $otherIds)->update(['is_representative' => false]);
                }

                // Always ensure representative flag on the chosen rep
                if (! $rep->is_representative) {
                    $rep->is_representative = true;
                }

                // Recalculate amount and vehicle_count only for pending context
                $pendingCount = $payments->where('status', 'pending')->count();
                if ($scope === 'pending' || $scope === 'all') {
                    // If there are pending items in this batch, reflect their count on the representative
                    if ($pendingCount > 0) {
                        $rep->vehicle_count = $pendingCount;
                        // Only override amount when pending to avoid changing paid receipts
                        if ($rep->status === 'pending') {
                            $rep->amount = $pendingCount * $fee;
                        }
                    }
                }

                if ($rep->isDirty()) {
                    $rep->save();
                    $reprAdjusted++;
                }

                $fixedBatches++;
            });
        }

        $this->info("Batches processed: {$fixedBatches}");
        $this->info("Representatives adjusted: {$reprAdjusted}");
        $this->info("Children demoted: {$childrenDemoted}");

        $this->info('Done.');

        return self::SUCCESS;
    }
}
