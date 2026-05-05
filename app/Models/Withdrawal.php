<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use HasFactory, SoftDeletes;

    // INI DIA SABUK PENGAMANNYA! Wajib diisi semua nama kolomnya.
    protected $fillable = [
        'employee_id',
        'officer_id',
        'amount',
        'status',
        'notes',
    ];

    /**
     * Relasi: Siapa karyawan yang narik duit ini?
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id')->withTrashed();
    }

    /**
     * Relasi: Siapa petugas HRGA yang memproses pencairan ini?
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id')->withTrashed();
    }
}
