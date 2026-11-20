<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Transaksi yang ditimbang mulai 8 Okt 2026 tapi masih tercatat pakai jenis & harga
 * lama → dipindah ke jenis list baru dan dihitung ulang pakai harga 8 Okt.
 * Jenis lama yang gak ada di pemetaan dibiarkan & dilaporkan (bukan ditebak).
 */
return new class extends Migration
{
    private const BERLAKU = '2026-10-08 00:00:00';

    // kode jenis lama => nama jenis list baru
    private const PEMETAAN = [
        'PLTK0001' => 'PET Bening',             // BOTOL PET A
        'PLTK0014' => 'Emberan',                // PLASTIK EMBERAN HITAM/ GABUNGAN
        'PLTK0016' => 'Tutup Botol',            // TUTUP HD
        'KRTS0001' => 'Koran / Majalah / Buku', // KORAN
        'KRTS0002' => 'Kardus',                 // KARDUS
        'LAIN0010' => 'Besi Campur',            // BESI A
        'LAIN0011' => 'Kaleng',                 // KALENG
        'LAIN0013' => 'Minyak Jelantah',        // MINYAK JELANTAH
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $items = DB::table('transaction_items as i')
                ->join('transactions as t', 't.id', '=', 'i.transaction_id')
                ->join('waste_types as w', 'w.id', '=', 'i.waste_type_id')
                ->where('t.weighing_at', '>=', self::BERLAKU)
                ->where('w.inactive_from', '<=', self::BERLAKU)
                ->get(['i.id', 'i.weight_kg', 'w.code', 'w.name']);

            $baru = DB::table('waste_types as w')
                ->join('waste_prices as p', 'p.waste_type_id', '=', 'w.id')
                ->where('p.effective_from', self::BERLAKU)
                ->whereIn('w.name', self::PEMETAAN)
                ->get(['w.id', 'w.name', 'p.price_per_kg'])
                ->keyBy('name');

            $dilewati = [];
            foreach ($items as $item) {
                $target = $baru->get(self::PEMETAAN[$item->code] ?? '');
                if (! $target) {
                    $dilewati[$item->code] = $item->name;

                    continue;
                }

                DB::table('transaction_items')->where('id', $item->id)->update([
                    'waste_type_id' => $target->id,
                    'price_at_time' => $target->price_per_kg,
                    'subtotal' => $item->weight_kg * $target->price_per_kg,
                    'updated_at' => now(),
                ]);
            }

            if ($dilewati) {
                echo 'PERHATIAN, jenis lama tanpa pemetaan (tidak dihitung ulang): '
                    .implode(', ', array_map(fn ($c, $n) => "$c $n", array_keys($dilewati), $dilewati)).PHP_EOL;
            }
        });
    }

    public function down(): void
    {
        // Harga lama gak disimpan terpisah; restore dari backup database kalau perlu balik.
    }
};
