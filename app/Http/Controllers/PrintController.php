<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Withdrawal;

class PrintController extends Controller
{
    public function withdrawal(Withdrawal $withdrawal)
    {
        $withdrawal->load(['employee.division', 'officer']);

        return view('print.withdrawal', compact('withdrawal'));
    }

    public function transaction(Transaction $transaction)
    {
        $transaction->load(['employee.division', 'officer', 'items.wasteType']);

        return view('print.transaction', compact('transaction'));
    }
}
