<?php

namespace App\Livewire\Transaction;

use App\Enums\WithdrawalStatus;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\TransactionService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[\Livewire\Attributes\Title('Pencairan Saldo')]
class WithdrawalCreate extends Component
{
    use WithPagination;

    public $history_search = '';

    public $history_status = '';

    public function updatingHistorySearch()
    {
        $this->resetPage();
    }

    public function updatingHistoryStatus()
    {
        $this->resetPage();
    }

    public $search_nik = '';

    public $employee;

    public $current_balance = 0;

    public $amount = '';

    public $notes = 'Pencairan Tabungan Sampah';

    // 1. CARI NASABAH + DIVISI (Eager Loading)
    public function updatedSearchNik($value)
    {
        // Pake with('division') biar departemennya langsung kebaca, gak perlu query lagi di Blade
        $this->employee = User::with('division')
            ->where('employee_code', $value)
            ->where('is_active', true)
            ->first();

        $this->current_balance = $this->employee?->balance ?? 0;
    }

    public function saveWithdrawal()
    {
        $throttleKey = 'withdrawal-submit-'.auth()->id();
        // env() di sini bakal null kalau config di-cache — wajib lewat config()
        $maxAttempts = (int) config('app.throttle.withdrawal.limit');
        $decaySeconds = (int) config('app.throttle.withdrawal.decay');

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            session()->flash('error', "Sistem mendeteksi terlalu banyak klik. Silakan tunggu {$seconds} detik.");

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, $decaySeconds);

        $this->validate([
            'employee' => 'required',
            'amount' => [
                'required',
                'numeric',
                'min:100000',
            ],
        ], [
            'employee.required' => 'NIK tidak ditemukan. Silakan cari nasabah terlebih dahulu.',
            'amount.min' => 'Aturan Pabrik: Minimal pencairan Rp 100.000!',
        ]);

        try {
            $withdrawal = app(TransactionService::class)->createWithdrawal(
                $this->employee, $this->amount, $this->notes, auth()->id(), WithdrawalStatus::PENDING
            );
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        ActivityLogger::log(
            'new_withdrawal',
            'Mengajukan pencairan Rp '.number_format($this->amount, 0, ',', '.').' untuk '.$this->employee->name." (NIK: {$this->employee->employee_code})",
            'Withdrawal',
            $withdrawal->id
        );

        // Flash session buat tombol cetak di Blade
        session()->flash('success', 'Pencairan dana '.$this->employee->name.' berhasil diajukan!');
        session()->flash('print_id', $withdrawal->id);

        // Reset Form tapi simpan session flash
        return redirect()->route('withdrawals.index');
    }

    public function render()
    {
        $recent_withdrawals = Withdrawal::with(['employee.division', 'officer'])
            ->when($this->history_search, fn ($q) => $q->whereHas('employee', function ($e) {
                $e->where('name', 'like', "%{$this->history_search}%")
                    ->orWhere('employee_code', 'like', "%{$this->history_search}%");
            }))
            ->when($this->history_status, fn ($q) => $q->where('status', $this->history_status))
            ->latest()
            ->paginate(10);

        return view('livewire.transaction.withdrawal-create', compact('recent_withdrawals'));
    }

    // Pengajuan dari karyawan: setujui jadi PENDING (lanjut transfer) atau tolak (saldo balik)
    public function approveRequest($id)
    {
        $this->decideRequest($id, WithdrawalStatus::PENDING, 'approve_withdrawal', 'Menyetujui');
    }

    public function rejectRequest($id)
    {
        $this->decideRequest($id, WithdrawalStatus::REJECTED, 'reject_withdrawal', 'Menolak');
    }

    private function decideRequest($id, WithdrawalStatus $status, string $action, string $verb): void
    {
        $withdrawal = Withdrawal::find($id);

        if (! $withdrawal || $withdrawal->status !== WithdrawalStatus::REQUESTED->value) {
            session()->flash('error', 'Pengajuan tidak ditemukan atau sudah diproses.');

            return;
        }

        $withdrawal->update(['status' => $status->value, 'officer_id' => auth()->id()]);

        ActivityLogger::log(
            $action,
            "{$verb} pengajuan pencairan Rp ".number_format($withdrawal->amount, 0, ',', '.').' dari '.$withdrawal->employee?->name,
            'Withdrawal',
            $withdrawal->id
        );

        session()->flash('success', "Pengajuan {$withdrawal->employee?->name} berstatus {$status->value}.");
    }

    // 3. SELESAIKAN TRANSFER (Update Status)
    public function completeWithdrawal($id)
    {
        $withdrawal = Withdrawal::find($id);

        if ($withdrawal && $withdrawal->status === WithdrawalStatus::PENDING->value) {
            $withdrawal->update(['status' => WithdrawalStatus::COMPLETED->value]);

            ActivityLogger::log(
                'complete_withdrawal',
                'Menyelesaikan pencairan Rp '.number_format($withdrawal->amount, 0, ',', '.').' untuk '.$withdrawal->employee?->name,
                'Withdrawal',
                $withdrawal->id
            );

            session()->flash('success', 'Status transfer sudah COMPLETED!');
        }
    }
}
