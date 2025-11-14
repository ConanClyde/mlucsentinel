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
        Schema::create('reporter_role_user_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_role_id')->constrained('reporter_roles')->onDelete('cascade');
            $table->string('user_type'); // 'student', 'staff', 'security', 'stakeholder', 'reporter'
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['reporter_role_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporter_role_user_type');
    }
};
