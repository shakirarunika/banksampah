<?php

namespace App\Livewire\VendorSale;

use App\Models\VendorSale;
use App\Models\VendorSaleItem;
use App\Models\WasteType;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public $vendorSaleId;
    public $transaction_date;
    public $vendor_name;
    public $receipt_photo;
    public $existing_photo;
    
    public $deduction_percentage = 0;
    public $deduction_reason = '';

    public $items = [];

    public function mount($id = null)
    {
        if ($id) {
            $sale = VendorSale::with('items')->findOrFail($id);
            $this->vendorSaleId = $sale->id;
            $this->transaction_date = $sale->transaction_date->format('Y-m-d');
            $this->vendor_name = $sale->vendor_name;
            $this->existing_photo = $sale->receipt_photo;
            $this->deduction_reason = $sale->deduction_reason;
            
            if ($sale->total_amount > 0 && $sale->deduction_amount > 0) {
                $this->deduction_percentage = round(($sale->deduction_amount / $sale->total_amount) * 100, 2);
            }
            
            foreach ($sale->items as $item) {
                $pricePerKg = $item->weight_kg > 0 ? $item->total_price / $item->weight_kg : 0;
                $this->items[] = [
                    'waste_type_id' => $item->waste_type_id,
                    'weight_kg' => $item->weight_kg,
                    'price_per_kg' => $pricePerKg,
                    'total_price' => $item->total_price,
                ];
            }
        } else {
            $this->transaction_date = now()->format('Y-m-d');
            $this->addItem(); // Default satu baris kosong
        }
    }

    public function updatedItems($value, $name)
    {
        $parts = explode('.', $name);
        if (count($parts) === 2) {
            $index = $parts[0];
            $field = $parts[1];

            if ($field === 'waste_type_id') {
                $waste = WasteType::with('currentPrice')->find($value);
                if ($waste && $waste->currentPrice) {
                    $this->items[$index]['price_per_kg'] = $waste->currentPrice->price_per_kg;
                } else {
                    $this->items[$index]['price_per_kg'] = 0;
                }
                $this->calculateItemTotal($index);
            } elseif ($field === 'weight_kg' || $field === 'price_per_kg') {
                $this->calculateItemTotal($index);
            }
        }
    }

    public function calculateItemTotal($index)
    {
        $weight = (float) ($this->items[$index]['weight_kg'] ?? 0);
        $pricePerKg = (float) ($this->items[$index]['price_per_kg'] ?? 0);
        $this->items[$index]['total_price'] = $weight * $pricePerKg;
    }

    public function addItem()
    {
        $this->items[] = [
            'waste_type_id' => '',
            'weight_kg' => '',
            'price_per_kg' => 0,
            'total_price' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        
        if (count($this->items) === 0) {
            $this->addItem();
        }
    }

    public function save()
    {
        $this->validate([
            'transaction_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'receipt_photo' => 'nullable|image|max:2048', // max 2MB
            'deduction_percentage' => 'nullable|numeric|min:0|max:100',
            'deduction_reason' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.waste_type_id' => 'required|exists:waste_types,id',
            'items.*.weight_kg' => 'required|numeric|min:0.01',
            'items.*.price_per_kg' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ], [
            'items.*.waste_type_id.required' => 'Kategori sampah harus dipilih.',
            'items.*.weight_kg.required' => 'Berat tidak boleh kosong.',
            'items.*.price_per_kg.required' => 'Harga / Kg tidak boleh kosong.',
            'items.*.total_price.required' => 'Total harga tidak boleh kosong.',
        ]);

        DB::transaction(function () {
            // Kalkulasi ulang matematika secara ketat di backend
            foreach ($this->items as &$item) {
                $item['total_price'] = (float) $item['weight_kg'] * (float) ($item['price_per_kg'] ?? 0);
            }

            $totalWeight = collect($this->items)->sum('weight_kg');
            $totalAmount = collect($this->items)->sum('total_price');
            
            $deductionAmount = $totalAmount * (($this->deduction_percentage ?: 0) / 100);

            $data = [
                'transaction_date' => $this->transaction_date,
                'vendor_name' => $this->vendor_name,
                'total_weight_kg' => $totalWeight,
                'total_amount' => $totalAmount,
                'deduction_amount' => $deductionAmount,
                'deduction_reason' => $this->deduction_reason,
            ];

            if ($this->receipt_photo) {
                $data['receipt_photo'] = $this->receipt_photo->store('receipts', 'public');
            }

            if ($this->vendorSaleId) {
                $sale = VendorSale::findOrFail($this->vendorSaleId);
                $sale->update($data);
                
                // Sinkronisasi item: Hapus yang lama, insert yang baru
                $sale->items()->delete();
                $sale->items()->createMany($this->items);
                
                session()->flash('message', 'Data penjualan vendor berhasil diupdate!');
            } else {
                $sale = VendorSale::create($data);
                $sale->items()->createMany($this->items);
                session()->flash('message', 'Data penjualan vendor berhasil ditambahkan!');
            }
        });

        return redirect()->route('vendor-sales.index');
    }

    public function render()
    {
        return view('livewire.vendor-sale.form', [
            'wasteTypes' => WasteType::where('is_active', true)->get()
        ])->layout('layouts.app');
    }
}
