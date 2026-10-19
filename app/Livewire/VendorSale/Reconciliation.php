<?php

namespace App\Livewire\VendorSale;

use App\Models\TransactionItem;
use App\Models\VendorSaleItem;
use App\Models\WasteType;
use Livewire\Component;

#[\Livewire\Attributes\Title('Rekonsiliasi Bulanan')]
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
        // Nilai dari client bisa ngaco (kosong / bulan 13) — jepit ke rentang valid
        $month = min(12, max(1, (int) $this->month));
        $year = min(2100, max(2000, (int) $this->year ?: now()->year));
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
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
        // Dijual - disetor: plus = berat bertambah (stok lama ikut terjual), minus = susut
        $diffKg = $totalOutboundKg - $totalInboundKg;
        $diffPercent = $totalInboundKg > 0 ? ($diffKg / $totalInboundKg) * 100 : 0;

        $profitMargin = $netOutboundPrice - $totalInboundPrice;

        // 4. Data per Kategori — berat saja; sampah gabungan dipilah ulang sebelum dijual,
        // jadi margin per kategori tidak bermakna (gabungan minus, kardus/PET plus). Margin hanya di total.
        $wasteTypes = WasteType::all();
        $comparisonData = $wasteTypes->map(function ($type) use ($inboundItems, $outboundItems) {
            $inKg = $inboundItems->where('waste_type_id', $type->id)->sum('weight_kg');
            $outKg = $outboundItems->where('waste_type_id', $type->id)->sum('weight_kg');

            return [
                'name' => $type->name,
                'inbound_kg' => $inKg,
                'outbound_kg' => $outKg,
                'diff_kg' => $outKg - $inKg,
            ];
        });

        return view('livewire.vendor-sale.reconciliation', compact(
            'totalInboundKg',
            'totalInboundPrice',
            'totalOutboundKg',
            'totalOutboundPrice',
            'totalDeductions',
            'netOutboundPrice',
            'diffKg',
            'diffPercent',
            'profitMargin',
            'comparisonData'
        ))->layout('layouts.app');
    }
}
