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
        Schema::create('stakeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('type_id')->nullable()->constrained('stakeholder_types')->onDelete('set null');
            $table->string('license_no')->nullable();
            $table->string('license_image')->nullable();
            $table->date('expiration_date')->nullable(); // expiration date of this user account, not the license (4 years)
            $table->timestamps();

            // Performance indexes
            $table->index(['type_id', 'license_no'], 'stakeholders_type_id_license_no_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stakeholders');
    }
};
