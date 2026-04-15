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
        Schema::create('waste_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_type_id')->constrained();
            $table->decimal('price_per_kg', 12, 2);
            $table->datetime('effective_from'); // Tanggal mulai berlaku harga [cite: 25]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_prices');
    }
};
