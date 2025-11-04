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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'sticker_fee'
            $table->string('display_name'); // e.g., 'Sticker Fee'
            $table->decimal('amount', 10, 2); // The fee amount
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default fees
        DB::table('fees')->insert([
            [
                'name' => 'sticker_fee',
                'display_name' => 'Sticker Fee',
                'amount' => 15.00,
                'description' => 'Fee for vehicle sticker registration',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
