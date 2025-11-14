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
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();

            // User Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('user_type'); // student, staff, stakeholder, security, reporter

            // License Information (Step 2)
            $table->string('license_no')->nullable(); // Driver's license number (optional)
            $table->string('license_image')->nullable(); // License image path (optional)

            // Reporter Information (for reporter user type only)
            $table->foreignId('reporter_role_id')->nullable()->index(); // Reporter role selection

            // Student Information (for student user type only)
            $table->foreignId('program_id')->nullable()->index(); // Program selection for students
            $table->string('student_id')->nullable()->unique(); // Student ID for students

            // Staff Information (for staff user type only)
            $table->string('staff_id')->nullable()->unique(); // Staff ID for staff

            // Security Information (for security user type only)
            $table->string('security_id')->nullable()->unique(); // Security ID for security

            // Stakeholder Information (for stakeholder user type only)
            $table->foreignId('stakeholder_type_id')->nullable()->index(); // Stakeholder type selection
            $table->string('guardian_evidence')->nullable(); // Guardian evidence file path for stakeholders

            // Vehicle Information (handled in separate pending_vehicles table)
            // Removed individual vehicle fields - using separate table for multiple vehicles

            // Registration Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();

            // Tracking
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Foreign key for reviewer
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');

            // Foreign key for reporter role
            $table->foreign('reporter_role_id')->references('id')->on('reporter_roles')->onDelete('set null');

            // Foreign key for program
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('set null');

            // Foreign key for stakeholder type
            $table->foreign('stakeholder_type_id')->references('id')->on('stakeholder_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};
