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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type',
                ['global_administrator',
                    'administrator',
                    'student',
                    'staff',
                    'security',
                    'reporter',
                    'stakeholder',
                ]);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('name')->virtualAs("CONCAT(first_name, ' ', last_name)");
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            // Performance indexes
            $table->index(['user_type', 'is_active'], 'users_user_type_is_active_index');
            $table->index(['email', 'is_active'], 'users_email_is_active_index');
            $table->index(['created_at', 'user_type'], 'users_created_at_user_type_index');
            $table->index('user_type', 'users_user_type_index');
            $table->index('is_active', 'users_is_active_index');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');

        Schema::enableForeignKeyConstraints();
    }
};
