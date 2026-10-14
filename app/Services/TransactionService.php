<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\WasteType;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a new transaction with its items.
     *
     * @param  array  $data  Transaction header data (employee_id, officer_id, weighing_at)
     * @param  array  $items  List of items (waste_type_id, weight). Harga & subtotal
     *                        selalu dihitung ulang di sini dari harga DB — nilai dari
     *                        client (Livewire public property) gak dipercaya.
     *
     * @throws Exception
     */
    public function createTransaction(array $data, array $items): Transaction
    {
        foreach ($items as $i => $item) {
            if (! is_numeric($item['weight'] ?? null) || $item['weight'] <= 0) {
                throw new Exception('Berat sampah harus lebih dari 0.');
            }

            $waste = WasteType::with('currentPrice')->find($item['waste_type_id'] ?? null);
            if (! $waste) {
                throw new Exception('Jenis sampah tidak valid.');
            }

            $items[$i]['price'] = $waste->currentPrice->price_per_kg ?? 0;
            $items[$i]['subtotal'] = $item['weight'] * $items[$i]['price'];
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'employee_id' => $data['employee_id'],
                'officer_id' => $data['officer_id'],
                'weighing_at' => $data['weighing_at'],
                'status' => TransactionStatus::POSTED,
            ]);

            foreach ($items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'waste_type_id' => $item['waste_type_id'],
                    'weight_kg' => $item['weight'],
                    'price_at_time' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Void a posted transaction safely.
     *
     * @throws Exception
     */
    public function voidTransaction(Transaction $transaction): bool
    {
        if ($transaction->status !== TransactionStatus::POSTED) {
            throw new Exception('Transaksi tidak valid atau sudah dibatalkan.');
        }

        $nilai_yang_mau_dihapus = $transaction->items->sum('subtotal');

        $saldo_sekarang = $transaction->employee->balance;

        if (($saldo_sekarang - $nilai_yang_mau_dihapus) < 0) {
            throw new Exception('GAGAL VOID! Saldo akan minus jika dibatalkan!');
        }

        return $transaction->update(['status' => TransactionStatus::CANCELLED]);
    }
}
