<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    case REQUESTED = 'REQUESTED'; // diajukan karyawan, nunggu persetujuan petugas
    case PENDING = 'PENDING';     // disetujui, nunggu transfer koperasi
    case COMPLETED = 'COMPLETED';
    case REJECTED = 'REJECTED';   // ditolak petugas, saldo balik

    /**
     * Status yang dihitung sebagai "uang sudah keluar" dari saldo.
     * REQUESTED ikut dihitung biar saldo yang lagi diajukan gak bisa diajukan dua kali.
     */
    public static function deducted(): array
    {
        return [self::REQUESTED->value, self::PENDING->value, self::COMPLETED->value];
    }
}
