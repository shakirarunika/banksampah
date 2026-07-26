<?php

namespace App\Livewire\Transaction;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\WasteType;
use DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
#[\Livewire\Attributes\Title('Edit Timbangan')]
class TransactionEdit extends Component
{
    public Transaction $transaction;

    public $items = []; // Untuk nampung list sampah yang diedit

    public $waste_types;

    public $employee_name;

    public function mount(Transaction $transaction)
    {
        $this->transaction = $transaction->load('items', 'employee');
        $this->employee_name = $transaction->employee->name;
        $this->waste_types = WasteType::all();

        // Load item yang sudah ada ke dalam array temporary
        foreach ($transaction->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'waste_type_id' => $item->waste_type_id,
                'weight_kg' => $item->weight_kg,
                'price_at_time' => $item->price_at_time,
            ];
        }
    }

    public function update()
    {
        // Guard: transaksi yang sudah di-void tidak boleh diedit
        if ($this->transaction->status === \App\Enums\TransactionStatus::CANCELLED) {
            session()->flash('error', 'Transaksi ini sudah dibatalkan (void) dan tidak dapat diedit.');

            return redirect()->route('transactions.index');
        }

        $this->validate([
            'items.*.waste_type_id' => 'required|exists:waste_types,id',
            'items.*.weight_kg' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // --- TENTUKAN HARGA PER ITEM ---
            // Item lama keep harga historisnya (price_at_time dari DB, bukan dari
            // client biar gak bisa di-tamper). Harga sekarang cuma buat item baru
            // atau item yang jenis sampahnya diganti.
            $originalItems = $this->transaction->items->keyBy('id');
            $pricedItems = [];
            $total_nilai_baru = 0;

            foreach ($this->items as $item) {
                $original = isset($item['id']) ? $originalItems->get($item['id']) : null;

                if ($original && $original->waste_type_id == $item['waste_type_id']) {
                    $price = $original->price_at_time;
                } else {
                    $waste = WasteType::with('currentPrice')->find($item['waste_type_id']);
                    $price = $waste->currentPrice->price_per_kg ?? 0;
                }

                $pricedItems[] = [
                    'waste_type_id' => $item['waste_type_id'],
                    'weight_kg' => $item['weight_kg'],
                    'price' => $price,
                ];
                $total_nilai_baru += $item['weight_kg'] * $price;
            }

            // --- VALIDASI SALDO BRUTAL ---
            $nilai_transaksi_ini_lama = $this->transaction->items->sum('subtotal');
            $saldo_tanpa_transaksi_ini = $this->transaction->employee->balance - $nilai_transaksi_ini_lama;

            if (($saldo_tanpa_transaksi_ini + $total_nilai_baru) < 0) {
                session()->flash('error', 'Gagal Update! Perubahan ini bikin saldo karyawan jadi minus.');

                return;
            }

            // --- EKSEKUSI UPDATE ---
            // Hapus item lama, ganti baru (cara paling bersih buat many-to-many edit)
            $this->transaction->items()->delete();

            foreach ($pricedItems as $itemData) {
                TransactionItem::create([
                    'transaction_id' => $this->transaction->id,
                    'waste_type_id' => $itemData['waste_type_id'],
                    'weight_kg' => $itemData['weight_kg'],
                    'price_at_time' => $itemData['price'],
                    'subtotal' => $itemData['weight_kg'] * $itemData['price'],
                ]);
            }

            DB::commit();
            session()->flash('message', 'Data timbangan berhasil diperbarui!');

            return redirect()->route('transactions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.transaction.transaction-edit');
    }
}
