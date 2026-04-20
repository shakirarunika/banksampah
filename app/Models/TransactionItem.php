<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'transaction_id',
        'waste_type_id',
        'weight_kg',
        'price_at_time',
        'subtotal',
    ];

    /**
     * Relasi ke Transaction: Transaksi utama yang menaungi rincian item ini.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relasi ke WasteType: Jenis sampah pada item ini.
     */
    public function wasteType(): BelongsTo
    {
        return $this->belongsTo(WasteType::class);
    }
}
