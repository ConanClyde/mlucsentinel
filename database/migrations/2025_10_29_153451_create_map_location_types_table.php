<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('map_location_types')) {
            Schema::create('map_location_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // Parking, Building, Emergency, Service, etc.
                $table->string('icon')->nullable(); // Icon class or emoji
                $table->string('default_color')->default('#3B82F6'); // Default color for this type
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();

                $table->index('is_active');
            });
        }

        // Insert or update default location types
        DB::table('map_location_types')->upsert([
            [
                'name' => 'Parking Zone',
                'icon' => 'P',
                'default_color' => '#10b981',
                'description' => 'Vehicle parking areas',
                'is_active' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Building',
                'icon' => 'B',
                'default_color' => '#3b82f6',
                'description' => 'Campus buildings and structures',
                'is_active' => true,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Emergency Station',
                'icon' => 'E',
                'default_color' => '#ef4444',
                'description' => 'Emergency response stations',
                'is_active' => true,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Service Area',
                'icon' => 'S',
                'default_color' => '#eab308',
                'description' => 'Service and maintenance areas',
                'is_active' => true,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Restricted Zone',
                'icon' => 'R',
                'default_color' => '#8b5cf6',
                'description' => 'Restricted access areas',
                'is_active' => true,
                'display_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['name'], ['icon', 'default_color', 'description', 'is_active', 'display_order', 'updated_at']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_location_types');
    }
};
