<?php

namespace App\Imports;

use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?Model
    {
        // withTrashed: NIK unik di DB, akun yang sudah dihapus dilewati (sama kayak karyawan:sync)
        // daripada bikin baru dan bentrok
        $user = User::withTrashed()->firstOrNew(['employee_code' => $row['nik']]);
        if ($user->trashed()) {
            return null;
        }
        $isNew = ! $user->exists;

        $user->fill([
            'name' => $row['nama_lengkap'],
            'email' => $row['email'] ?? $row['nik'].'@dasiaya.com',
        ]);

        // Nama divisi kosong jangan dicari — LIKE '%%' nyangkut ke divisi pertama
        $divisionName = trim($row['nama_divisi'] ?? '');
        if ($divisionName !== '') {
            $user->division_id = Division::where('name', 'like', '%'.$divisionName.'%')->value('id');
        }

        // Role cuma diubah kalau kolomnya diisi, biar Excel tanpa kolom role gak nurunin admin.
        // Role ngaco/typo jatuh ke karyawan.
        $role = strtolower(trim($row['role'] ?? ''));
        if ($role !== '') {
            $user->role = in_array($role, User::ROLES, true) ? $role : 'karyawan';
        }

        // User lama: status aktif & password gak disentuh (karyawan resign tetap nonaktif)
        if ($isNew) {
            $user->role ??= 'karyawan';
            $user->is_active = true;
            $user->password = Hash::make($row['nik']);
            $user->must_change_password = true;
        }

        $user->save();

        return $user;
    }
}
