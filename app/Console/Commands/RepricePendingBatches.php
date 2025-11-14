<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\PaymentBatchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepricePendingBatches extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payments:reprice-pending {--batch_id=}';

    /**
     * The console command description.
     */
    protected $description = 'Recalculate representative amount and vehicle_count for pending batches';

    public function handle(): int
    {
        $batchId = $this->option('batch_id');
        $svc = app(PaymentBatchService::class);
        $editor = 'System';

        if ($batchId) {
            $this->repriceBatch($svc, $batchId, $editor);

            return self::SUCCESS;
        }

        $batchIds = Payment::query()
            ->where('status', 'pending')
            ->whereNotNull('batch_id')
            ->select('batch_id')
            ->distinct()
            ->pluck('batch_id');

        if ($batchIds->isEmpty()) {
            $this->info('No pending batches to reprice.');

            return self::SUCCESS;
        }

        foreach ($batchIds as $b) {
            $this->repriceBatch($svc, $b, $editor);
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    protected function repriceBatch(PaymentBatchService $svc, string $batchId, string $editor): void
    {
        DB::beginTransaction();
        try {
            $rep = $svc->repriceBatch($batchId, $editor);
            if ($rep) {
                $this->info("Repriced batch {$batchId}: vehicles={$rep->vehicle_count}, amount={$rep->amount}");
            } else {
                $this->warn("Batch {$batchId} has no pending payments.");
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Failed to reprice {$batchId}: ".$e->getMessage());
        }
    }
}
