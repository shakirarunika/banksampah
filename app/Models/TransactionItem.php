<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    // Pastiin mass assignment-nya udah bener kayak error sebelumnya
    protected $fillable = [
        'transaction_id',
        'waste_type_id',
        'weight_kg',
        'price_at_time',
        'subtotal'
    ];

    // INI DIA OBAT BUAT ERROR LO TADI: 
    // Relasi balik dari Rincian Item (Anak) ke Transaksi Utama (Induk)
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    // Relasi ke Master Sampah
    public function wasteType(): BelongsTo
    {
        return $this->belongsTo(WasteType::class);
    }
}
