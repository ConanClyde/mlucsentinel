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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_id')->constrained('colleges')->onDelete('cascade');
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();

            // Performance indexes
            $table->index('college_id', 'programs_college_id_index');
            $table->index(['college_id', 'name'], 'programs_college_id_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
