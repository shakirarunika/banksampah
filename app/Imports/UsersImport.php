<?php

namespace App\Imports;

use App\Models\Division;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Cari ID divisi berdasarkan nama yang diketik di Excel (Case Insensitive)
        $division = Division::where('name', 'like', '%'.$row['nama_divisi'].'%')->first();

        // 2. Gunakan firstOrNew dan assign explicit
        $user = User::firstOrNew(['employee_code' => $row['nik']]);
        
        $user->fill([
            'name' => $row['nama_lengkap'],
            'email' => $row['email'] ?? $row['nik'].'@dasiaya.com',
            'division_id' => $division->id ?? null,
        ]);

        // TIPS: Hanya set password jika user-nya beneran baru (biar password lama gak keriset)
        if (!$user->exists) {
            $user->password = Hash::make($row['nik']);
        }

        // Set kolom yang tidak ada di $fillable secara manual
        $user->role = strtolower($row['role'] ?? 'karyawan');
        $user->is_active = true;
        
        $user->save();

        return $user;
    }
}
