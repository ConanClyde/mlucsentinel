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
        Schema::create('pending_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pending_registration_id')->constrained('pending_registrations')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('vehicle_types')->onDelete('restrict');
            $table->string('plate_no')->nullable(); // Some vehicle types don't require plates
            $table->timestamps();

            // Indexes
            $table->index('pending_registration_id');
            $table->index('type_id');
            $table->index('plate_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_vehicles');
    }
};
