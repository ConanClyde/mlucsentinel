<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('map_location_types', function (Blueprint $table) {
            $table->boolean('requires_polygon')->default(true)->after('default_color');
        });

        // Update existing records: Most location types require polygons by default
        // We'll set all existing ones to true since they're likely already using polygons
        DB::table('map_location_types')->update(['requires_polygon' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('map_location_types', function (Blueprint $table) {
            $table->dropColumn('requires_polygon');
        });
    }
};
