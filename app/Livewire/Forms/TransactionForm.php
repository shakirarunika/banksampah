<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;

class TransactionForm extends Form
{
    // Data Transaksi
    #[Validate('required|date|before_or_equal:today')]
    public $transaction_date = '';

    // Form Input Keranjang Sementara
    public $selected_waste = '';
    public $weight = '';

    // Isi Keranjang (Cart)
    public $items = [];

    public function initDefaultDate()
    {
        $this->transaction_date = now()->format('Y-m-d');
    }

    public function addItem($waste)
    {
        $this->validate([
            'selected_waste' => 'required',
            'weight' => 'required|numeric|min:0.01',
        ], [
            'selected_waste.required' => 'Silakan pilih jenis sampah terlebih dahulu.',
            'weight.required' => 'Berat sampah tidak boleh kosong.',
            'weight.min' => 'Berat minimal harus 0.01 kg.',
        ]);

        $price = $waste->currentPrice->price_per_kg ?? 0;
        $subtotal = $this->weight * $price;

        $this->items[] = [
            'waste_type_id' => $waste->id,
            'waste_name' => $waste->name,
            'weight' => (float) $this->weight,
            'price' => $price,
            'subtotal' => $subtotal,
        ];

        $this->reset(['selected_waste', 'weight']);
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }
}
