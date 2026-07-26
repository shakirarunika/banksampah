<?php

namespace App\Livewire\Transaction;

use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
#[\Livewire\Attributes\Title('Pencairan Saldo')]
class WithdrawalCreate extends Component
{
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

        DB::beginTransaction();
        try {
            // Lock data nasabah secara pesimis untuk mencegah Race Condition,
            // lalu hitung ulang saldo di dalam transaksi DB
            $lockedEmployee = User::where('id', $this->employee->id)->lockForUpdate()->first();
            $saldo_aktual = $lockedEmployee->balance;

            // Validasi saldo secara atomik
            if ($this->amount > $saldo_aktual) {
                DB::rollBack();
                session()->flash('error', 'Saldo tidak mencukupi! Saldo aktual: Rp '.number_format($saldo_aktual, 0, ',', '.'));

                return;
            }

            $withdrawal = Withdrawal::create([
                'employee_id' => $this->employee->id,
                'officer_id' => auth()->id(),
                'amount' => $this->amount,
                'status' => \App\Enums\WithdrawalStatus::PENDING->value,
                'notes' => $this->notes,
            ]);

            DB::commit();

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
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal Simpan: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Tampilkan 5 riwayat terbaru di tabel bawah
        $recent_withdrawals = Withdrawal::with(['employee.division', 'officer'])
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.transaction.withdrawal-create', compact('recent_withdrawals'));
    }

    // 3. SELESAIKAN TRANSFER (Update Status)
    public function completeWithdrawal($id)
    {
        $withdrawal = Withdrawal::find($id);

        if ($withdrawal && $withdrawal->status === \App\Enums\WithdrawalStatus::PENDING->value) {
            $withdrawal->update(['status' => \App\Enums\WithdrawalStatus::COMPLETED->value]);

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
