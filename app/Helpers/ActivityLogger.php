<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Catat satu baris log aktivitas.
     *
     * @param string      $action      Kode aksi singkat, e.g. 'void_transaction'
     * @param string      $description Kalimat human-readable, e.g. "Membatalkan transaksi #42 milik Budi"
     * @param string|null $subjectType Nama class model yang dikenai aksi, e.g. 'Transaction'
     * @param int|null    $subjectId   ID record yang dikenai aksi
     */
    public static function log(
        string $action,
        string $description,
        ?string $subjectType = null,
        ?int $subjectId = null
    ): void {
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
        ]);
    }
}
