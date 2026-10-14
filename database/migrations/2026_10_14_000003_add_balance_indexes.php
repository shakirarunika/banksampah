<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * User::balance, mutasi, laporan & leaderboard semuanya filter by
     * employee_id + status — index gabungan biar gak full scan.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['employee_id', 'status']);
        });
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'status']);
        });
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'status']);
        });
    }
};
