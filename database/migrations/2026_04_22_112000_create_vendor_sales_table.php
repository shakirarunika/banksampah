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
        Schema::create('vendor_sales', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->foreignId('waste_type_id')->constrained('waste_types');
            $table->decimal('weight_kg', 10, 2);
            $table->decimal('total_price', 15, 2);
            $table->string('vendor_name');
            $table->string('receipt_photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_sales');
    }
};
