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
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->boolean('requires_plate')->default(true)->after('name');
        });

        // Update existing records: Motorcycle and Car require plates, Electric Vehicle does not
        DB::table('vehicle_types')->where('name', 'Motorcycle')->update(['requires_plate' => true]);
        DB::table('vehicle_types')->where('name', 'Car')->update(['requires_plate' => true]);
        DB::table('vehicle_types')->where('name', 'Electric Vehicle')->update(['requires_plate' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_types', function (Blueprint $table) {
            $table->dropColumn('requires_plate');
        });
    }
};
