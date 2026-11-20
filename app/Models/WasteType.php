<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'unit', 'is_active', 'inactive_from'];

    protected $casts = ['inactive_from' => 'datetime'];

    /**
     * Relasi: Satu jenis sampah punya banyak riwayat harga
     */
    public function prices()
    {
        return $this->hasMany(WastePrice::class)->latest('effective_from')->latest('id');
    }

    /**
     * Harga yang berlaku SAAT INI (harga bertanggal masa depan belum dihitung).
     */
    public function currentPrice(): HasOne
    {
        return $this->hasOne(WastePrice::class)->ofMany(
            ['effective_from' => 'max', 'id' => 'max'],
            fn ($q) => $q->where('effective_from', '<=', now()->format('Y-m-d H:i:s'))
        );
    }

    /**
     * Jenis sampah yang boleh dipakai buat penimbangan di tanggal $date:
     * sudah punya harga di tanggal itu & belum diganti list baru (inactive_from).
     */
    public function scopeUsableOn(Builder $query, $date): Builder
    {
        $date = self::dayEnd($date);

        return $query
            ->whereHas('prices', fn ($q) => $q->where('effective_from', '<=', $date))
            ->where(fn ($q) => $q->whereNull('inactive_from')->orWhere('inactive_from', '>', $date));
    }

    /** Harga per kg yang berlaku di tanggal $date, null kalau belum ada harga. */
    public function priceAt($date): ?float
    {
        $price = $this->prices()
            ->where('effective_from', '<=', self::dayEnd($date))
            ->value('price_per_kg');

        return $price === null ? null : (float) $price;
    }

    // Harga yang mulai berlaku di suatu hari berlaku untuk seluruh hari itu,
    // jadi input "tanggal saja" (00:00) tetap dapat harga yang diset siang harinya.
    private static function dayEnd($date): string
    {
        return \Carbon\Carbon::parse($date)->endOfDay()->format('Y-m-d H:i:s');
    }

    /** Untuk dropdown: eager-load harga yang berlaku di $date ke tiap jenis. */
    public static function withPriceOn($date, ?Builder $query = null)
    {
        $d = self::dayEnd($date);

        return ($query ?? static::query())
            ->with(['prices' => fn ($q) => $q->where('effective_from', '<=', $d)])
            ->orderBy('code')
            ->get()
            ->each(fn ($w) => $w->price_on_date = $w->prices->first()?->price_per_kg);
    }
}
