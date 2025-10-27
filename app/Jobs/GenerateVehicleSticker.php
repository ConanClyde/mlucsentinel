<?php

namespace App\Jobs;

use App\Models\Vehicle;
use App\Services\StickerGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateVehicleSticker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Vehicle $vehicle,
        public string $stickerNumber,
        public string $color
    ) {}

    /**
     * Execute the job.
     */
    public function handle(StickerGenerator $stickerGenerator): void
    {
        try {
            Log::info("Generating sticker for vehicle {$this->vehicle->id}");

            $stickerPath = $stickerGenerator->generateVehicleSticker(
                $this->stickerNumber,
                $this->vehicle->type->name ?? 'Vehicle',
                $this->vehicle->plate_no,
                $this->color,
                $this->vehicle->id
            );

            $this->vehicle->update(['sticker' => $stickerPath]);

            Log::info("Sticker generated successfully for vehicle {$this->vehicle->id}: {$stickerPath}");
        } catch (\Exception $e) {
            Log::error("Failed to generate sticker for vehicle {$this->vehicle->id}: ".$e->getMessage());
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Sticker generation failed after {$this->tries} attempts for vehicle {$this->vehicle->id}: ".$exception->getMessage());
    }
}
