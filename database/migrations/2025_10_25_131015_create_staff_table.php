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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('staff_id')->unique();
            $table->string('license_no')->nullable();
            $table->string('license_image')->nullable();
            $table->date('expiration_date')->nullable(); // expiration date of this user account, not the license (4 years)
            $table->timestamps();

            // Performance indexes
            $table->index('staff_id', 'staff_staff_id_index');
            $table->index('license_no', 'staff_license_no_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
