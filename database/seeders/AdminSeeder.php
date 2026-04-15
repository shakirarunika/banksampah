<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // MENTOR HACK: Bikin akun Admin anti-gagal
        User::updateOrCreate(
            ['email' => 'faishal.muhammad@cimory.com'], // Cek biar gak duplikat kalau lo run 2 kali
            [
                'name' => 'Master Admin',
                'employee_code' => '00000', // Kode khusus dewa
                'password' => Hash::make('rahasia123'), // Default password lo
                'role' => 'admin', // Pastikan ini sesuai sama pengaturan role/permission lo!
                'is_active' => true,
            ]
        );
    }
}
