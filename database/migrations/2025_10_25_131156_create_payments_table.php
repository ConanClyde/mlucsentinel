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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('sticker_fee');
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('reference')->nullable()->unique();
            $table->string('batch_id')->nullable();
            $table->integer('vehicle_count')->default(1);
            $table->boolean('is_representative')->default(true);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'type']);
            $table->index('paid_at');
            $table->index('batch_id');

            // Performance indexes
            $table->index('created_at', 'payments_created_at_index');
            $table->index(['user_id', 'status'], 'payments_user_id_status_index');
            $table->index(['status', 'created_at'], 'payments_status_created_at_index');
            $table->index(['batch_id', 'status'], 'payments_batch_id_status_index');
            $table->index(['status', 'is_representative', 'created_at'], 'payments_status_rep_created_at_index');
            $table->index(['batch_id', 'status', 'created_at'], 'payments_batch_status_created_at_index');

            // Generated column for unique batch representative constraint
            $table->string('rep_batch_id')->nullable()->storedAs('IF(is_representative, batch_id, NULL)');
            $table->unique('rep_batch_id', 'uniq_payments_rep_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
