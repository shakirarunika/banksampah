<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Withdrawal;
use App\Enums\TransactionStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    /**
     * Create a new transaction with its items.
     *
     * @param array $data Transaction header data (employee_id, officer_id, weighing_at)
     * @param array $items List of items (waste_type_id, weight, price, subtotal)
     * @return Transaction
     * @throws Exception
     */
    public function createTransaction(array $data, array $items): Transaction
    {
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
     * @param Transaction $transaction
     * @return bool
     * @throws Exception
     */
    public function voidTransaction(Transaction $transaction): bool
    {
        if ($transaction->status !== TransactionStatus::POSTED) {
            throw new Exception('Transaksi tidak valid atau sudah dibatalkan.');
        }

        $nilai_yang_mau_dihapus = $transaction->items->sum('subtotal');

        $total_masuk = TransactionItem::whereHas('transaction', function ($q) use ($transaction) {
            $q->where('employee_id', $transaction->employee_id)
                ->where('status', TransactionStatus::POSTED);
        })->sum('subtotal');

        $total_keluar = Withdrawal::where('employee_id', $transaction->employee_id)
            ->whereIn('status', ['PENDING', 'COMPLETED'])
            ->sum('amount');

        $saldo_sekarang = $total_masuk - $total_keluar;

        if (($saldo_sekarang - $nilai_yang_mau_dihapus) < 0) {
            throw new Exception('GAGAL VOID! Saldo akan minus jika dibatalkan!');
        }

        return $transaction->update(['status' => TransactionStatus::CANCELLED]);
    }
}
