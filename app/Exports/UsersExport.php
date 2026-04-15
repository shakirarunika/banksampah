<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $isTemplate;

    public function __construct($isTemplate = false)
    {
        $this->isTemplate = $isTemplate;
    }

    /**
     * Ambil data dari database
     */
    public function collection()
    {
        // Kalau cuma butuh template, balikin koleksi kosong
        if ($this->isTemplate) {
            return collect([]);
        }

        return User::with('division')->get();
    }

    /**
     * Header kolom di Excel
     */
    public function headings(): array
    {
        return [
            'NIK',
            'NAMA_LENGKAP',
            'EMAIL',
            'ROLE',
            'NAMA_DIVISI'
        ];
    }

    /**
     * Mapping data ke kolom yang sesuai
     */
    public function map($user): array
    {
        return [
            $user->employee_code,
            $user->name,
            $user->email,
            $user->role,
            $user->division->name ?? '-',
        ];
    }
}
