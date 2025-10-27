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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('type_id')->nullable()->constrained('vehicle_types')->onDelete('set null');
            $table->string('plate_no')->nullable();
            $table->string('color'); // color of sticker
            $table->string('number'); // number of sticker (not unique, all starts at 0001)
            $table->string('sticker')->nullable(); // sticker image
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Add unique constraint on color and number
            $table->unique(['color', 'number']);
            $table->index(['user_id', 'type_id']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
