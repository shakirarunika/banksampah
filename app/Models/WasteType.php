<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'unit'];

    /**
     * Relasi: Satu jenis sampah punya banyak riwayat harga
     */
    public function prices()
    {
        return $this->hasMany(WastePrice::class)->latest('effective_from');
    }

    /**
     * Relasi: Ambil harga TERBARU yang berlaku saat ini
     * INI SOLUSI ERRORNYA: Harus balikkan HasOne menggunakan latestOfMany
     */
    public function currentPrice(): HasOne
    {
        return $this->hasOne(WastePrice::class)->latestOfMany('effective_from');
    }
}
