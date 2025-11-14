<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sticker_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('student_expiration_years')->default(4);
            $table->unsignedTinyInteger('staff_expiration_years')->default(4);
            $table->unsignedTinyInteger('security_expiration_years')->default(4);
            $table->unsignedTinyInteger('stakeholder_expiration_years')->default(4);
            $table->string('staff_color', 64)->default('maroon');
            $table->string('security_color', 64)->default('maroon');
            $table->json('student_map')->nullable();
            $table->json('stakeholder_map')->nullable();
            $table->json('palette')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sticker_rules');
    }
};
