<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_sale_id',
        'waste_type_id',
        'weight_kg',
        'total_price',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function vendorSale(): BelongsTo
    {
        return $this->belongsTo(VendorSale::class);
    }

    public function wasteType(): BelongsTo
    {
        return $this->belongsTo(WasteType::class);
    }
}
