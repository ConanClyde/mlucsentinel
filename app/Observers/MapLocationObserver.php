<?php

namespace App\Observers;

use App\Models\MapLocation;
use App\Services\MapStickerGenerator;
use Illuminate\Support\Facades\Storage;

class MapLocationObserver
{
    public function __construct(
        protected MapStickerGenerator $stickerGenerator
    ) {}

    /**
     * Handle the MapLocation "created" event.
     */
    public function created(MapLocation $mapLocation): void
    {
        // Generate sticker after creation
        try {
            $stickerPath = $this->stickerGenerator->generateLocationSticker($mapLocation);
            $mapLocation->updateQuietly(['sticker_path' => $stickerPath]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate sticker for location '.$mapLocation->id.': '.$e->getMessage());
        }
    }

    /**
     * Handle the MapLocation "updated" event.
     */
    public function updated(MapLocation $mapLocation): void
    {
        // Regenerate sticker if short_code, name, or color changed
        if ($mapLocation->wasChanged(['short_code', 'name', 'color'])) {
            try {
                $this->stickerGenerator->regenerateSticker($mapLocation);
            } catch (\Exception $e) {
                \Log::error('Failed to regenerate sticker for location '.$mapLocation->id.': '.$e->getMessage());
            }
        }
    }

    /**
     * Handle the MapLocation "deleted" event.
     */
    public function deleted(MapLocation $mapLocation): void
    {
        // Delete the sticker file when location is deleted
        if ($mapLocation->sticker_path) {
            try {
                $stickerPath = str_replace('/storage/', '', $mapLocation->sticker_path);
                if (Storage::disk('public')->exists($stickerPath)) {
                    Storage::disk('public')->delete($stickerPath);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to delete sticker for location '.$mapLocation->id.': '.$e->getMessage());
            }
        }
    }

    /**
     * Handle the MapLocation "restored" event.
     */
    public function restored(MapLocation $mapLocation): void
    {
        // Regenerate sticker when location is restored
        try {
            $this->stickerGenerator->regenerateSticker($mapLocation);
        } catch (\Exception $e) {
            \Log::error('Failed to regenerate sticker for restored location '.$mapLocation->id.': '.$e->getMessage());
        }
    }

    /**
     * Handle the MapLocation "force deleted" event.
     */
    public function forceDeleted(MapLocation $mapLocation): void
    {
        // Delete the sticker file when location is force deleted
        if ($mapLocation->sticker_path) {
            try {
                $stickerPath = str_replace('/storage/', '', $mapLocation->sticker_path);
                if (Storage::disk('public')->exists($stickerPath)) {
                    Storage::disk('public')->delete($stickerPath);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to delete sticker for force deleted location '.$mapLocation->id.': '.$e->getMessage());
            }
        }
    }
}
