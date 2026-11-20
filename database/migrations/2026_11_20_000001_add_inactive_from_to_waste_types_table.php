<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jenis sampah gak dipakai lagi mulai tanggal ini (penimbangan backdate sebelum tanggal ini tetap bisa pakai)
        Schema::table('waste_types', function (Blueprint $table) {
            $table->dateTime('inactive_from')->nullable()->after('is_active');
        });

        // Seragamkan format "2026-03-05T16:01" → "2026-03-05 16:01:00": harga dicari per tanggal lewat perbandingan string
        DB::table('waste_prices')->where('effective_from', 'like', '%T%')->get(['id', 'effective_from'])
            ->each(fn ($p) => DB::table('waste_prices')->where('id', $p->id)->update([
                'effective_from' => \Carbon\Carbon::parse($p->effective_from)->format('Y-m-d H:i:s'),
            ]));
    }

    public function down(): void
    {
        Schema::table('waste_types', function (Blueprint $table) {
            $table->dropColumn('inactive_from');
        });
    }
};
