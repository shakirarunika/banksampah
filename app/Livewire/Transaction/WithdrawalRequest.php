<?php

namespace App\Livewire\Transaction;

use App\Enums\WithdrawalStatus;
use App\Helpers\ActivityLogger;
use App\Services\TransactionService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
#[\Livewire\Attributes\Title('Ajukan Pencairan')]
class WithdrawalRequest extends Component
{
    public $amount = '';

    public $notes = '';

    public function submit()
    {
        $throttleKey = 'withdrawal-submit-'.auth()->id();
        if (RateLimiter::tooManyAttempts($throttleKey, (int) config('app.throttle.withdrawal.limit'))) {
            session()->flash('error', 'Terlalu banyak percobaan. Silakan tunggu '.RateLimiter::availableIn($throttleKey).' detik.');

            return;
        }
        RateLimiter::hit($throttleKey, (int) config('app.throttle.withdrawal.decay'));

        $this->validate([
            'amount' => 'required|numeric|min:100000',
            'notes' => 'nullable|string|max:255',
        ], [
            'amount.min' => 'Minimal pencairan Rp 100.000!',
        ]);

        try {
            $withdrawal = app(TransactionService::class)->createWithdrawal(
                auth()->user(), $this->amount, $this->notes ?: 'Pengajuan pencairan karyawan', null, WithdrawalStatus::REQUESTED
            );
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        ActivityLogger::log(
            'request_withdrawal',
            'Mengajukan pencairan Rp '.number_format($this->amount, 0, ',', '.'),
            'Withdrawal',
            $withdrawal->id
        );

        $this->reset('amount', 'notes');
        session()->flash('success', 'Pengajuan terkirim! Petugas akan memprosesnya.');
    }

    public function render()
    {
        return view('livewire.transaction.withdrawal-request', [
            'balance' => auth()->user()->balance,
            'requests' => auth()->user()->withdrawals()->latest()->limit(10)->get(),
        ]);
    }
}
