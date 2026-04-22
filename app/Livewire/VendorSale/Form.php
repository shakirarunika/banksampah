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
    
    public $items = [];

    public function mount($id = null)
    {
        if ($id) {
            $sale = VendorSale::with('items')->findOrFail($id);
            $this->vendorSaleId = $sale->id;
            $this->transaction_date = $sale->transaction_date->format('Y-m-d');
            $this->vendor_name = $sale->vendor_name;
            $this->existing_photo = $sale->receipt_photo;
            
            foreach ($sale->items as $item) {
                $this->items[] = [
                    'waste_type_id' => $item->waste_type_id,
                    'weight_kg' => $item->weight_kg,
                    'total_price' => $item->total_price,
                ];
            }
        } else {
            $this->transaction_date = now()->format('Y-m-d');
            $this->addItem(); // Default satu baris kosong
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'waste_type_id' => '',
            'weight_kg' => '',
            'total_price' => '',
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
            'items' => 'required|array|min:1',
            'items.*.waste_type_id' => 'required|exists:waste_types,id',
            'items.*.weight_kg' => 'required|numeric|min:0.01',
            'items.*.total_price' => 'required|numeric|min:0',
        ], [
            'items.*.waste_type_id.required' => 'Kategori sampah harus dipilih.',
            'items.*.weight_kg.required' => 'Berat tidak boleh kosong.',
            'items.*.total_price.required' => 'Total harga tidak boleh kosong.',
        ]);

        DB::transaction(function () {
            $totalWeight = collect($this->items)->sum('weight_kg');
            $totalAmount = collect($this->items)->sum('total_price');

            $data = [
                'transaction_date' => $this->transaction_date,
                'vendor_name' => $this->vendor_name,
                'total_weight_kg' => $totalWeight,
                'total_amount' => $totalAmount,
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
