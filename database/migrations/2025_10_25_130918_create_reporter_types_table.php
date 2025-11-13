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
        // Clean up legacy schema if present.
        if (Schema::hasTable('reporters') && Schema::hasColumn('reporters', 'type_id')) {
            Schema::table('reporters', function (Blueprint $table) {
                $table->dropForeign(['type_id']);
                $table->dropColumn('type_id');
            });
        }

        if (Schema::hasTable('reporters') && Schema::hasColumn('reporters', 'expiration_date')) {
            Schema::table('reporters', function (Blueprint $table) {
                $table->dropColumn('expiration_date');
            });
        }

        Schema::dropIfExists('reporter_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('reporter_types')) {
            Schema::create('reporter_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('reporters')) {
            $missingTypeId = ! Schema::hasColumn('reporters', 'type_id');
            $missingExpirationDate = ! Schema::hasColumn('reporters', 'expiration_date');

            if ($missingTypeId || $missingExpirationDate) {
                Schema::table('reporters', function (Blueprint $table) use ($missingTypeId, $missingExpirationDate) {
                    if ($missingTypeId) {
                        $table->foreignId('type_id')->nullable()->after('user_id')->constrained('reporter_types')->nullOnDelete();
                    }

                    if ($missingExpirationDate) {
                        $table->date('expiration_date')->nullable()->after('reporter_role_id');
                    }
                });
            }
        }
    }
};
