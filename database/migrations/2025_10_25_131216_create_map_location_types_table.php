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
        Schema::create('map_location_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Parking, Building, Emergency, Service, etc.
            $table->string('icon')->nullable(); // Icon class or emoji
            $table->string('default_color')->default('#3B82F6'); // Default color for this type
            $table->boolean('requires_polygon')->default(true);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_location_types');
    }
};
