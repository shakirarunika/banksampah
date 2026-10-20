<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

// Satu baris per item sampah, biar bisa di-pivot/SUM di Excel
class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Collection $transactions) {}

    public function collection(): Collection
    {
        return $this->transactions->flatMap(fn ($trx) => $trx->items->map(fn ($item) => [$trx, $item]));
    }

    public function headings(): array
    {
        return ['ID_TRANSAKSI', 'TANGGAL_TIMBANG', 'NIK', 'NAMA', 'DIVISI', 'JENIS_SAMPAH', 'BERAT_KG', 'HARGA_PER_KG', 'SUBTOTAL_RP', 'STATUS', 'PETUGAS'];
    }

    public function map($row): array
    {
        [$trx, $item] = $row;

        return [
            $trx->id,
            $trx->weighing_at->format('Y-m-d'),
            $trx->employee?->employee_code ?? '-',
            $trx->employee?->name ?? 'User Terhapus',
            $trx->employee?->division?->name ?? '-',
            $item->wasteType?->name ?? '-',
            (float) $item->weight_kg,
            (float) $item->price_at_time,
            (float) $item->subtotal,
            $trx->status->label(),
            $trx->officer?->name ?? '-',
        ];
    }
}
