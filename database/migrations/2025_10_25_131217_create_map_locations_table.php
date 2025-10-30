<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('map_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('map_location_types')->onDelete('cascade');
            $table->string('name'); // Full location name
            $table->string('short_code', 10)->nullable(); // Short code like "P1", "MB", "E1"
            $table->text('description')->nullable();
            $table->string('color')->default('#3B82F6'); // Hex color for the polygon overlay

            // Polygon vertices (array of {x, y} coordinates as percentage-based positions)
            $table->json('vertices'); // Array of points: [{x: 10.5, y: 20.3}, {x: 15.2, y: 25.8}, ...]

            // Center point for label display (calculated from vertices)
            $table->decimal('center_x', 8, 4)->nullable(); // Center X position (0-100%)
            $table->decimal('center_y', 8, 4)->nullable(); // Center Y position (0-100%)

            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Indexes for performance
            $table->index('type_id');
            $table->index('is_active');
            $table->index(['type_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_locations');
    }
};
