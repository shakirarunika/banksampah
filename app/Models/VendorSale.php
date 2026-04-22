<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_date',
        'vendor_name',
        'total_weight_kg',
        'total_amount',
        'deduction_amount',
        'deduction_reason',
        'receipt_photo',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_weight_kg' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(VendorSaleItem::class);
    }

    public function getNetAmountAttribute()
    {
        return $this->total_amount - $this->deduction_amount;
    }
}
