<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WastePrice extends Model
{
    // datetime cast: input "2026-10-08T00:00" dari form disimpan seragam "Y-m-d H:i:s",
    // penting karena pencarian harga per tanggal membandingkan string di SQLite
    protected $casts = ['effective_from' => 'datetime'];

    // Ini 'daftar izin' yang dicari Laravel
    protected $fillable = [
        'waste_type_id',
        'price_per_kg',
        'effective_from',
        'effective_to',
        'user_id',
        'created_by',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wasteType()
    {
        return $this->belongsTo(WasteType::class);
    }
}
