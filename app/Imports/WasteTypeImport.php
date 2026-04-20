<?php

namespace App\Imports;

use App\Models\WastePrice;
use App\Models\WasteType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WasteTypeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Update atau Buat Jenis Sampah
        $waste = WasteType::updateOrCreate(
            ['code' => $row['kode_sampah']],
            [
                'name' => $row['nama_sampah'],
                'unit' => $row['satuan'] ?? 'kg',
            ]
        );

        // 2. Logic Audit Trail Harga
        $newPrice = (float) $row['harga_per_kg'];
        $lastPrice = $waste->currentPrice?->price_per_kg ?? 0;

        if ($newPrice != $lastPrice) {
            WastePrice::create([
                'waste_type_id' => $waste->id,
                'price_per_kg' => $newPrice,
                'effective_from' => now(),
            ]);
        }

        return $waste;
    }
}
