<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Cari ID divisi berdasarkan nama yang diketik di Excel (Case Insensitive)
        $division = Division::where('name', 'like', '%' . $row['nama_divisi'] . '%')->first();

        // 2. Gunakan updateOrCreate
        // Argumen 1: Kolom kunci untuk mencari data (NIK)
        // Argumen 2: Data yang mau diupdate atau dibuat baru
        return User::updateOrCreate(
            ['employee_code' => $row['nik']], // Kunci Pencarian
            [
                'name'          => $row['nama_lengkap'],
                'email'         => $row['email'] ?? $row['nik'] . '@dasiaya.com',

                // TIPS: Hanya set password jika user-nya beneran baru (biar password lama gak keriset)
                'password'      => Hash::make($row['nik']),

                'role'          => strtolower($row['role'] ?? 'karyawan'),
                'division_id'   => $division->id ?? null,
                'is_active'     => true,
            ]
        );
    }
}
