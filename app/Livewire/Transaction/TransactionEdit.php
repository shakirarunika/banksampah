<?php

namespace App\Livewire\Transaction;

use App\Models\Transaction;
use App\Models\WasteType;
use App\Models\Withdrawal;
use App\Models\TransactionItem;
use Livewire\Component;
use Livewire\Attributes\Layout;
use DB;

#[Layout('layouts.app')]
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
        $this->validate([
            'items.*.waste_type_id' => 'required|exists:waste_types,id',
            'items.*.weight_kg' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // --- VALIDASI SALDO BRUTAL ---
            $total_nilai_baru = 0;
            foreach ($this->items as $item) {
                $waste = WasteType::with('currentPrice')->find($item['waste_type_id']);
                $total_nilai_baru += $item['weight_kg'] * ($waste->currentPrice->price_per_kg ?? 0);
            }

            $total_masuk_lama = TransactionItem::whereHas('transaction', function ($q) {
                $q->where('employee_id', $this->transaction->employee_id)->where('status', 'POSTED');
            })->sum('subtotal');

            $total_keluar = Withdrawal::where('employee_id', $this->transaction->employee_id)
                ->whereIn('status', ['PENDING', 'COMPLETED'])->sum('amount');

            $nilai_transaksi_ini_lama = $this->transaction->items->sum('subtotal');
            $saldo_tanpa_transaksi_ini = $total_masuk_lama - $total_keluar - $nilai_transaksi_ini_lama;

            if (($saldo_tanpa_transaksi_ini + $total_nilai_baru) < 0) {
                session()->flash('error', 'Gagal Update! Perubahan ini bikin saldo karyawan jadi minus.');
                return;
            }

            // --- EKSEKUSI UPDATE ---
            // Hapus item lama, ganti baru (cara paling bersih buat many-to-many edit)
            $this->transaction->items()->delete();

            foreach ($this->items as $itemData) {
                $waste = WasteType::with('currentPrice')->find($itemData['waste_type_id']);
                $price = $waste->currentPrice->price_per_kg ?? 0;

                TransactionItem::create([
                    'transaction_id' => $this->transaction->id,
                    'waste_type_id' => $itemData['waste_type_id'],
                    'weight_kg' => $itemData['weight_kg'],
                    'price_at_time' => $price,
                    'subtotal' => $itemData['weight_kg'] * $price,
                ]);
            }

            DB::commit();
            session()->flash('message', 'Data timbangan berhasil diperbarui!');
            return redirect()->route('transactions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.transaction.transaction-edit');
    }
}
