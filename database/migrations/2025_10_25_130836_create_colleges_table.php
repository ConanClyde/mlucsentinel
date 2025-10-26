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
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert default colleges
        $now = now();
        DB::table('colleges')->insert([
            ['name' => 'College of Graduate Studies', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'College of Law', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'College of Engineering', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'College of Information Technology', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'College of Arts and Sciences', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'College of Education', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'College of Management', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Institute of Criminal Justice Education', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'College of Technology', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};
