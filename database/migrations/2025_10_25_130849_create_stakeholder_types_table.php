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
        Schema::create('stakeholder_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert default stakeholder types
        $now = now();
        DB::table('stakeholder_types')->insert([
            ['name' => 'Guardian', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Service Provider', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Visitor', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stakeholder_types');
    }
};
