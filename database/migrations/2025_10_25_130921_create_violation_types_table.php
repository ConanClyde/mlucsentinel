<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('violation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('violation_types')->insert([
            ['name' => 'Improper Parking', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Blocking Driveway', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Parking on Non Designated Area', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Parking on corners', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Disrespecting Personnel in Authority', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'No ID Presented / Use of Other Student\'s ID', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Improper School Attire', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_types');
    }
};
