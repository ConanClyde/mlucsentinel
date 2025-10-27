<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add "Noisy Muffler (Tambutso)" violation type
        $now = now();
        DB::table('violation_types')->insert([
            'name' => 'Noisy Muffler (Tambutso)',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the "Noisy Muffler (Tambutso)" violation type
        DB::table('violation_types')->where('name', 'Noisy Muffler (Tambutso)')->delete();
    }
};
