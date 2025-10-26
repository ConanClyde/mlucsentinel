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
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('area'); // area, building, parking, etc.
            $table->string('color')->default('#3B82F6'); // Hex color for the territory
            $table->json('vertices'); // Array of {x, y} coordinates for polygon vertices
            $table->decimal('center_x', 10, 2)->nullable(); // Center point X (for label)
            $table->decimal('center_y', 10, 2)->nullable(); // Center point Y (for label)
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
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
