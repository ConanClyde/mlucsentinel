<?php

namespace App\Console\Commands;

use App\Models\MapLocation;
use App\Models\MapLocationType;
use Illuminate\Console\Command;

class TestMapStickerGeneration extends Command
{
    protected $signature = 'test:map-sticker';

    protected $description = 'Test map location sticker generation';

    public function handle(): int
    {
        $this->info('Testing Map Location Sticker Generation...');

        // Check if we have any location types
        $type = MapLocationType::first();
        if (! $type) {
            $this->warn('No map location types found. Creating a test type...');
            $type = MapLocationType::create([
                'name' => 'Test Building',
                'icon' => 'building',
                'default_color' => '#007BFF',
                'description' => 'Test type for sticker generation',
                'is_active' => true,
                'display_order' => 1,
            ]);
        }

        // Create a test location
        $this->info('Creating test location...');
        $location = MapLocation::create([
            'type_id' => $type->id,
            'name' => 'Test Building '.now()->format('H:i:s'),
            'short_code' => 'TB-'.rand(100, 999),
            'description' => 'Test location for sticker generation',
            'color' => 'blue',
            'vertices' => [
                ['x' => 100, 'y' => 100],
                ['x' => 200, 'y' => 100],
                ['x' => 200, 'y' => 200],
                ['x' => 100, 'y' => 200],
            ],
            'is_active' => true,
            'display_order' => 1,
        ]);

        // Refresh to get the sticker_path
        $location->refresh();

        if ($location->sticker_path) {
            $this->info('✓ Sticker generated successfully!');
            $this->info('Location ID: '.$location->id);
            $this->info('Short Code: '.$location->short_code);
            $this->info('Sticker Path: '.$location->sticker_path);

            // Check if file actually exists
            $filePath = public_path(str_replace('/storage/', 'storage/', $location->sticker_path));
            if (file_exists($filePath)) {
                $this->info('✓ Sticker file exists on disk');
                $this->info('File size: '.number_format(filesize($filePath)).' bytes');
            } else {
                $this->error('✗ Sticker file NOT found on disk');
            }
        } else {
            $this->error('✗ Sticker generation failed - no sticker_path set');

            return self::FAILURE;
        }

        // Test regeneration
        $this->newLine();
        $this->info('Testing sticker regeneration by changing short code...');
        $oldPath = $location->sticker_path;
        $location->update(['short_code' => 'TB-'.rand(1000, 9999)]);
        $location->refresh();

        if ($location->sticker_path && $location->sticker_path !== $oldPath) {
            $this->info('✓ Sticker regenerated successfully!');
            $this->info('New Short Code: '.$location->short_code);
            $this->info('New Sticker Path: '.$location->sticker_path);
        } else {
            $this->warn('Sticker may not have regenerated (paths are the same or null)');
        }

        // Cleanup
        $this->newLine();
        if ($this->confirm('Delete test location?', true)) {
            $location->delete();
            $this->info('Test location deleted (sticker should be auto-deleted too)');
        } else {
            $this->info('Test location kept (ID: '.$location->id.')');
        }

        return self::SUCCESS;
    }
}
