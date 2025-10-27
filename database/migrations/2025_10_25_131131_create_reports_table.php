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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('violator_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();

            $table->string('violator_sticker_number')->nullable();
            $table->foreignId('violation_type_id')->constrained('violation_types')->onDelete('restrict');
            $table->text('description');
            $table->string('location');

            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('assigned_to_user_type')->nullable(); // For reference: 'Administrator', 'Security', etc.

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamp('reported_at')->useCurrent();
            $table->string('evidence_image')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('reported_at');
            $table->index('violation_type_id');
            $table->index(['status', 'reported_at']);
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
