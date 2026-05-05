<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'officer_id',
        'weighing_at',
        'status', // PENDING, POSTED, CANCELLED
    ];

    protected $casts = [
        'weighing_at' => 'datetime',
        'status' => \App\Enums\TransactionStatus::class,
    ];

    /**
     * Relasi ke User: Nasabah/Karyawan pemilik transaksi ini.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id')->withTrashed();
    }

    /**
     * Relasi ke User: Petugas yang mencatat transaksi.
     */
    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id')->withTrashed();
    }

    /**
     * Relasi ke TransactionItem: Detail item sampah dalam transaksi.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
