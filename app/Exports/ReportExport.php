<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['NIK', 'NAMA', 'DIVISI', 'SETORAN_KG', 'SETORAN_RP', 'PENCAIRAN_RP'];
    }

    public function map($user): array
    {
        return [
            $user->employee_code,
            $user->name,
            $user->division->name ?? '-',
            (float) $user->setoran_kg,
            (float) $user->setoran_rp,
            (float) $user->pencairan_rp,
        ];
    }
}
