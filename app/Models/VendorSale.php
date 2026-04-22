<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_date',
        'waste_type_id',
        'weight_kg',
        'total_price',
        'vendor_name',
        'receipt_photo',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'weight_kg' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function wasteType()
    {
        return $this->belongsTo(WasteType::class);
    }
}
