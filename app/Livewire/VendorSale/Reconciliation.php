<?php

namespace App\Livewire\VendorSale;

use App\Models\TransactionItem;
use App\Models\VendorSaleItem;
use App\Models\WasteType;
use Livewire\Component;

class Reconciliation extends Component
{
    public $month;
    public $year;

    public function mount()
    {
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
    }

    public function render()
    {
        $startDate = \Carbon\Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        // 1. Ambil data agregat Inbound (Setoran Karyawan) bulan ini
        $inboundItems = TransactionItem::whereHas('transaction', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('weighing_at', [$startDate, $endDate])
              // Hanya hitung yang sudah POSTED / bukan CANCELLED
              ->where('status', '!=', \App\Enums\TransactionStatus::CANCELLED->value);
        })->get();

        $totalInboundKg = $inboundItems->sum('weight_kg');
        $totalInboundPrice = $inboundItems->sum('subtotal');

        // 2. Ambil data agregat Outbound (Penjualan Vendor) bulan ini
        $outboundItems = VendorSaleItem::whereHas('vendorSale', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()]);
        })->get();

        $outboundSales = \App\Models\VendorSale::whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])->get();

        $totalOutboundKg = $outboundItems->sum('weight_kg');
        $totalOutboundPrice = $outboundItems->sum('total_price'); // Gross total untuk tabel rincian
        $totalDeductions = $outboundSales->sum('deduction_amount');
        $netOutboundPrice = $outboundSales->sum('net_amount'); // Net total untuk hitung profit margin

        // 3. Hitung Kalkulasi
        $shrinkageKg = $totalInboundKg - $totalOutboundKg;
        $shrinkagePercent = $totalInboundKg > 0 ? ($shrinkageKg / $totalInboundKg) * 100 : 0;
        
        $profitMargin = $netOutboundPrice - $totalInboundPrice;

        // 4. Data per Kategori
        $wasteTypes = WasteType::all();
        $comparisonData = $wasteTypes->map(function ($type) use ($inboundItems, $outboundItems) {
            $inKg = $inboundItems->where('waste_type_id', $type->id)->sum('weight_kg');
            $inPrice = $inboundItems->where('waste_type_id', $type->id)->sum('subtotal');

            $outKg = $outboundItems->where('waste_type_id', $type->id)->sum('weight_kg');
            $outPrice = $outboundItems->where('waste_type_id', $type->id)->sum('total_price');

            return [
                'name' => $type->name,
                'inbound_kg' => $inKg,
                'inbound_price' => $inPrice,
                'outbound_kg' => $outKg,
                'outbound_price' => $outPrice,
                'shrinkage_kg' => $inKg - $outKg,
                'profit' => $outPrice - $inPrice,
            ];
        });

        return view('livewire.vendor-sale.reconciliation', compact(
            'totalInboundKg',
            'totalInboundPrice',
            'totalOutboundKg',
            'totalOutboundPrice',
            'totalDeductions',
            'shrinkageKg',
            'shrinkagePercent',
            'profitMargin',
            'comparisonData'
        ))->layout('layouts.app');
    }
}
