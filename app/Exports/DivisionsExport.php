<?php

namespace App\Exports;

use App\Models\Division;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DivisionsExport implements FromCollection, WithHeadings
{
    protected $isTemplate;

    public function __construct($isTemplate = false)
    {
        $this->isTemplate = $isTemplate;
    }

    public function collection(): Collection
    {
        return $this->isTemplate ? collect([]) : Division::all(['name']);
    }

    public function headings(): array
    {
        return ['NAMA_DIVISI'];
    }
}
