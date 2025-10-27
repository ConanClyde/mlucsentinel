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
        // Vehicles table indexes
        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('sticker', 'vehicles_sticker_index'); // For QR code lookups
        });

        // Reports table indexes
        Schema::table('reports', function (Blueprint $table) {
            $table->index(['assigned_to', 'status'], 'reports_assigned_to_status_index'); // For admin dashboards
            $table->index('violator_sticker_number', 'reports_violator_sticker_number_index'); // For report lookups
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['user_type', 'is_active'], 'users_user_type_is_active_index'); // For filtering active users by type
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index('created_at', 'payments_created_at_index'); // For date range queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex('vehicles_sticker_index');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_assigned_to_status_index');
            $table->dropIndex('reports_violator_sticker_number_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_user_type_is_active_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_created_at_index');
        });
    }
};
