<?php

namespace App\Imports;

use App\Models\Division;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DivisionsImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?Model
    {
        return Division::updateOrCreate(
            ['name' => $row['nama_divisi']], // Cari berdasarkan nama
            ['name' => $row['nama_divisi']]  // Kalau gak ada, buat baru
        );
    }
}
