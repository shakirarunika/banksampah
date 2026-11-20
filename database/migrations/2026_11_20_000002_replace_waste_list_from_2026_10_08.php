<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daftar sampah & harga baru berlaku 8 Okt 2026.
 * List lama TIDAK dihapus/diubah (riwayat transaksi masih nunjuk ke sana), cuma
 * di-set inactive_from = 8 Okt → masih bisa dipakai buat input backdate sebelum tanggal itu.
 */
return new class extends Migration
{
    private const BERLAKU = '2026-10-08 00:00:00';

    private const LIST = [
        'KRTS' => [
            ['Kardus', 1700], ['Kertas Campuran', 700], ['Koran / Majalah / Buku', 1200],
            ['Kertas Putih', 1400], ['Tetra Pak', 200],
        ],
        'PLTK' => [
            ['PET Bening', 2500], ['PET Galon', 2500], ['PET Warna', 1000], ['Emberan', 500],
            ['Gelas Warna', 800], ['Botol Oli', 1000], ['HD Mainan Warna', 1000], ['HD Mainan Hitam', 1000],
            ['HD Naso', 3000], ['Tutup Botol', 2000], ['Paralon', 300], ['Styrofoam', 1000],
            ['Styrofoam Makanan', 500], ['Kemasan Minyak Goreng / Mika', 50], ['Multilayer', 50],
            ['Sedotan', 50], ['PP Inject', 3000], ['HD Asoy / Plastik Kresek', 250], ['PE Bening', 3000],
            ['PE Melar', 2500], ['PE Hitam Kotor', 2000], ['PE Sablon Kotor', 2000],
            ['PE Bubble Wrap', 2000], ['PE Warna', 2000],
        ],
        'LAIN' => [
            ['Aluminium Soft Drink', 2000], ['Kaleng', 1000], ['Beling / Kaca', 150],
            ['Besi Campur', 1000], ['Minyak Jelantah', 5000],
        ],
        'CMRY' => [
            ['Cimory - Kemasan CYD (Botol PET)', 500], ['Cimory - Tutup Botol (CYD, dll)', 2000],
            ['Cimory - Pouch CYD / Multilayer', 50], ['Cimory - Karton / Dus CYD / FM / Kardus', 1700],
            ['Cimory - Kemasan CYD UHT / FM (Tetra Pak)', 200],
        ],
    ];

    public function up(): void
    {
        // Sudah pernah jalan (mis. di-rollback lalu migrate lagi) → jangan dobel
        if (DB::table('waste_prices')->where('effective_from', self::BERLAKU)->exists()) {
            return;
        }

        DB::transaction(function () {
            $now = now();

            DB::table('waste_types')->whereNull('inactive_from')->update([
                'is_active' => false,
                'inactive_from' => self::BERLAKU,
                'updated_at' => $now,
            ]);

            foreach (self::LIST as $prefix => $items) {
                $last = DB::table('waste_types')->where('code', 'like', $prefix.'%')->max('code');
                $n = $last ? (int) substr($last, strlen($prefix)) : 0;

                foreach ($items as [$name, $price]) {
                    $id = DB::table('waste_types')->insertGetId([
                        'code' => $prefix.str_pad(++$n, 4, '0', STR_PAD_LEFT),
                        'name' => $name,
                        'unit' => 'Kg',
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('waste_prices')->insert([
                        'waste_type_id' => $id,
                        'price_per_kg' => $price,
                        'effective_from' => self::BERLAKU,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $ids = DB::table('waste_prices')->where('effective_from', self::BERLAKU)->pluck('waste_type_id');
            if (DB::table('transaction_items')->whereIn('waste_type_id', $ids)->exists()) {
                throw new RuntimeException('List baru sudah dipakai transaksi, tidak bisa di-rollback.');
            }
            DB::table('waste_prices')->whereIn('waste_type_id', $ids)->delete();
            DB::table('waste_types')->whereIn('id', $ids)->delete();
            DB::table('waste_types')->where('inactive_from', self::BERLAKU)
                ->update(['is_active' => true, 'inactive_from' => null]);
        });
    }
};
