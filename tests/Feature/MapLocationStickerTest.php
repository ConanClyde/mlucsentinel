<?php

namespace Tests\Feature;

use App\Models\MapLocation;
use App\Models\MapLocationType;
use App\Models\User;
use App\Services\MapStickerGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MapLocationStickerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected MapLocationType $locationType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a global administrator user
        $this->admin = User::factory()->create([
            'user_type' => 'global_administrator',
            'is_active' => true,
        ]);

        // Create a map location type
        $this->locationType = MapLocationType::create([
            'name' => 'Building',
            'icon' => 'building',
            'default_color' => '#007BFF',
            'description' => 'Campus buildings',
            'is_active' => true,
            'display_order' => 1,
        ]);

        // Fake the storage
        Storage::fake('public');
    }

    public function test_sticker_is_generated_when_location_is_created(): void
    {
        // Create a map location
        $location = MapLocation::create([
            'type_id' => $this->locationType->id,
            'name' => 'Main Building',
            'short_code' => 'MB-001',
            'description' => 'Main administrative building',
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

        // Refresh the model to get the sticker_path
        $location->refresh();

        // Assert sticker path was set
        $this->assertNotNull($location->sticker_path);
        $this->assertStringContainsString('/storage/map-stickers/', $location->sticker_path);

        // Assert sticker file exists in storage
        $stickerPath = str_replace('/storage/', '', $location->sticker_path);
        Storage::disk('public')->assertExists($stickerPath);

        // Assert file is SVG
        $this->assertStringEndsWith('.svg', $location->sticker_path);
    }

    public function test_sticker_contains_short_code(): void
    {
        $location = MapLocation::create([
            'type_id' => $this->locationType->id,
            'name' => 'Library',
            'short_code' => 'LIB-01',
            'description' => 'University Library',
            'color' => 'green',
            'vertices' => [
                ['x' => 50, 'y' => 50],
                ['x' => 150, 'y' => 50],
                ['x' => 150, 'y' => 150],
                ['x' => 50, 'y' => 150],
            ],
            'is_active' => true,
            'display_order' => 1,
        ]);

        $location->refresh();

        // Get sticker content
        $stickerPath = str_replace('/storage/', '', $location->sticker_path);
        $stickerContent = Storage::disk('public')->get($stickerPath);

        // Assert short code is in the SVG content
        $this->assertStringContainsString('LIB-01', $stickerContent);
        $this->assertStringContainsString('MLUC CAMPUS MAP', $stickerContent);
    }

    public function test_sticker_is_regenerated_when_short_code_changes(): void
    {
        $location = MapLocation::create([
            'type_id' => $this->locationType->id,
            'name' => 'Gym',
            'short_code' => 'GYM-01',
            'description' => 'Sports Gymnasium',
            'color' => 'red',
            'vertices' => [
                ['x' => 10, 'y' => 10],
                ['x' => 110, 'y' => 10],
                ['x' => 110, 'y' => 110],
                ['x' => 10, 'y' => 110],
            ],
            'is_active' => true,
            'display_order' => 1,
        ]);

        $location->refresh();
        $originalStickerPath = $location->sticker_path;

        // Update short code
        $location->update(['short_code' => 'GYM-02']);
        $location->refresh();

        // Sticker should be regenerated
        $this->assertNotNull($location->sticker_path);

        // Get new sticker content
        $stickerPath = str_replace('/storage/', '', $location->sticker_path);
        $stickerContent = Storage::disk('public')->get($stickerPath);

        // New short code should be in sticker
        $this->assertStringContainsString('GYM-02', $stickerContent);
    }

    public function test_sticker_is_deleted_when_location_is_deleted(): void
    {
        $location = MapLocation::create([
            'type_id' => $this->locationType->id,
            'name' => 'Cafeteria',
            'short_code' => 'CAF-01',
            'description' => 'Main cafeteria',
            'color' => 'orange',
            'vertices' => [
                ['x' => 20, 'y' => 20],
                ['x' => 120, 'y' => 20],
                ['x' => 120, 'y' => 120],
                ['x' => 20, 'y' => 120],
            ],
            'is_active' => true,
            'display_order' => 1,
        ]);

        $location->refresh();
        $stickerPath = str_replace('/storage/', '', $location->sticker_path);

        // Verify sticker exists
        Storage::disk('public')->assertExists($stickerPath);

        // Delete location
        $location->delete();

        // Sticker should be deleted
        Storage::disk('public')->assertMissing($stickerPath);
    }

    public function test_sticker_generator_service_works_directly(): void
    {
        $location = MapLocation::create([
            'type_id' => $this->locationType->id,
            'name' => 'Test Building',
            'short_code' => 'TB-001',
            'description' => 'Test',
            'color' => 'purple',
            'vertices' => [
                ['x' => 0, 'y' => 0],
                ['x' => 100, 'y' => 0],
                ['x' => 100, 'y' => 100],
                ['x' => 0, 'y' => 100],
            ],
            'is_active' => true,
            'display_order' => 1,
        ]);

        $generator = app(MapStickerGenerator::class);
        $stickerPath = $generator->generateLocationSticker($location);

        $this->assertNotNull($stickerPath);
        $this->assertStringContainsString('/storage/map-stickers/', $stickerPath);

        // Verify file exists
        $filePath = str_replace('/storage/', '', $stickerPath);
        Storage::disk('public')->assertExists($filePath);
    }

    public function test_sticker_has_correct_color_background(): void
    {
        $location = MapLocation::create([
            'type_id' => $this->locationType->id,
            'name' => 'Blue Building',
            'short_code' => 'BB-01',
            'description' => 'Blue colored building',
            'color' => 'blue',
            'vertices' => [
                ['x' => 0, 'y' => 0],
                ['x' => 50, 'y' => 0],
                ['x' => 50, 'y' => 50],
                ['x' => 0, 'y' => 50],
            ],
            'is_active' => true,
            'display_order' => 1,
        ]);

        $location->refresh();
        $stickerPath = str_replace('/storage/', '', $location->sticker_path);
        $stickerContent = Storage::disk('public')->get($stickerPath);

        // Blue should be #007BFF
        $this->assertStringContainsString('#007BFF', $stickerContent);
    }
}
