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
        Schema::table('vendor_sales', function (Blueprint $table) {
            $table->decimal('deduction_amount', 15, 2)->default(0)->after('total_amount');
            $table->string('deduction_reason')->nullable()->after('deduction_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_sales', function (Blueprint $table) {
            $table->dropColumn(['deduction_amount', 'deduction_reason']);
        });
    }
};
