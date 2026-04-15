<?php

namespace App\Exports;

use App\Models\WasteType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WasteTypeExport implements FromCollection, WithHeadings, WithMapping
{
    protected $isTemplate;

    public function __construct($isTemplate = false)
    {
        $this->isTemplate = $isTemplate;
    }

    public function collection()
    {
        return $this->isTemplate ? collect([]) : WasteType::with('currentPrice')->get();
    }

    public function headings(): array
    {
        return ['KODE_SAMPAH', 'NAMA_SAMPAH', 'SATUAN', 'HARGA_PER_KG'];
    }

    public function map($waste): array
    {
        return [
            $waste->code,
            $waste->name,
            $waste->unit ?? 'kg',
            $waste->currentPrice->price_per_kg ?? 0,
        ];
    }
}
