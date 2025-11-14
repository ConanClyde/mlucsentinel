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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('college_id')->constrained('colleges')->onDelete('cascade');
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('set null');
            $table->string('student_id')->unique();
            $table->string('license_no')->nullable();
            $table->string('license_image')->nullable();
            $table->date('expiration_date')->nullable(); // expiration date of this user account, not the license (4 years)
            $table->timestamps();

            // Performance indexes
            $table->index(['student_id', 'college_id'], 'students_student_id_college_id_index');
            $table->index('license_no', 'students_license_no_index');
            $table->index('program_id', 'students_program_id_index');
            $table->index(['college_id', 'program_id'], 'students_college_id_program_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
