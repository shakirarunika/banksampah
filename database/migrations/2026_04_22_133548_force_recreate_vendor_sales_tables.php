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
        // 1. Drop existing tables if they exist to prevent conflicts
        Schema::dropIfExists('vendor_sale_items');
        Schema::dropIfExists('vendor_sales');

        // 2. Recreate parent table
        Schema::create('vendor_sales', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('vendor_name');
            $table->decimal('total_weight_kg', 10, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('receipt_photo')->nullable();
            $table->timestamps();
        });

        // 3. Recreate child table
        Schema::create('vendor_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('waste_type_id')->constrained('waste_types');
            $table->decimal('weight_kg', 10, 2);
            $table->decimal('total_price', 15, 2); // Harga spesifik untuk item ini
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_sale_items');
        Schema::dropIfExists('vendor_sales');
    }
};
