<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'officer_id',
        'weighing_at',
        'status', // PENDING, POSTED, CANCELLED
    ];

    protected $casts = [
        'weighing_at' => 'datetime',
    ];

    /**
     * Relasi: Transaksi ini milik si Karyawan (yang punya sampah)
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Relasi: Transaksi ini dicatat oleh si Petugas
     * INI YANG TADI HILANG DAN BIKIN ERROR
     */
    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    /**
     * Relasi: Satu transaksi punya banyak item sampah (kardus, plastik, dll)
     */
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
