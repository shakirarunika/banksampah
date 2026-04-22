<?php

namespace App\Livewire\VendorSale;

use App\Models\VendorSale;
use App\Models\WasteType;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public $vendorSaleId;
    public $transaction_date;
    public $waste_type_id;
    public $weight_kg;
    public $total_price;
    public $vendor_name;
    public $receipt_photo;
    public $existing_photo;

    public function mount($id = null)
    {
        if ($id) {
            $sale = VendorSale::findOrFail($id);
            $this->vendorSaleId = $sale->id;
            $this->transaction_date = $sale->transaction_date->format('Y-m-d');
            $this->waste_type_id = $sale->waste_type_id;
            $this->weight_kg = $sale->weight_kg;
            $this->total_price = $sale->total_price;
            $this->vendor_name = $sale->vendor_name;
            $this->existing_photo = $sale->receipt_photo;
        } else {
            $this->transaction_date = now()->format('Y-m-d');
        }
    }

    public function save()
    {
        $this->validate([
            'transaction_date' => 'required|date',
            'waste_type_id' => 'required|exists:waste_types,id',
            'weight_kg' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'vendor_name' => 'required|string|max:255',
            'receipt_photo' => 'nullable|image|max:2048', // max 2MB
        ]);

        $data = [
            'transaction_date' => $this->transaction_date,
            'waste_type_id' => $this->waste_type_id,
            'weight_kg' => $this->weight_kg,
            'total_price' => $this->total_price,
            'vendor_name' => $this->vendor_name,
        ];

        if ($this->receipt_photo) {
            $data['receipt_photo'] = $this->receipt_photo->store('receipts', 'public');
        }

        if ($this->vendorSaleId) {
            VendorSale::findOrFail($this->vendorSaleId)->update($data);
            session()->flash('message', 'Data penjualan vendor berhasil diupdate!');
        } else {
            VendorSale::create($data);
            session()->flash('message', 'Data penjualan vendor berhasil ditambahkan!');
        }

        return redirect()->route('vendor-sales.index');
    }

    public function render()
    {
        return view('livewire.vendor-sale.form', [
            'wasteTypes' => WasteType::where('is_active', true)->get()
        ])->layout('layouts.app');
    }
}
