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
        Schema::create('security', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('security_id')->unique();
            $table->string('license_no')->nullable();
            $table->string('license_image')->nullable();
            $table->date('expiration_date')->nullable(); // expiration date of this user account, not the license
            $table->timestamps();

            // Performance indexes
            $table->index('security_id', 'security_security_id_index');
            $table->index('license_no', 'security_license_no_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security');
    }
};
