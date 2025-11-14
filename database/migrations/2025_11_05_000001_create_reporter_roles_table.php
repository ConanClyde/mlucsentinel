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
        Schema::create('reporter_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->integer('default_expiration_years')->nullable()->comment('Default expiration period in years');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasTable('reporters') && Schema::hasColumn('reporters', 'reporter_role_id')) {
            Schema::table('reporters', function (Blueprint $table) {
                $table->foreign('reporter_role_id')
                    ->references('id')
                    ->on('reporter_roles')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reporters') && Schema::hasColumn('reporters', 'reporter_role_id')) {
            Schema::table('reporters', function (Blueprint $table) {
                $table->dropForeign(['reporter_role_id']);
            });
        }

        Schema::dropIfExists('reporter_roles');
    }
};
