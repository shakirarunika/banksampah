<?php

namespace App\Livewire\Transaction;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\TransactionItem;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
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

        if ($this->employee) {
            // Hitung total uang masuk (Hanya yang POSTED)
            $total_masuk = TransactionItem::whereHas('transaction', function ($q) {
                $q->where('employee_id', $this->employee->id)->where('status', 'POSTED');
            })->sum('subtotal');

            // Hitung total uang keluar (PENDING & COMPLETED dianggap sudah keluar biar gak double tarik)
            $total_keluar = Withdrawal::where('employee_id', $this->employee->id)
                ->whereIn('status', ['PENDING', 'COMPLETED'])
                ->sum('amount');

            $this->current_balance = $total_masuk - $total_keluar;
        } else {
            $this->current_balance = 0;
        }
    }

    // 2. SIMPAN PENCAIRAN (KEJAM & AMAN)
    public function saveWithdrawal()
    {
        $this->validate([
            'employee' => 'required',
            'amount' => [
                'required',
                'numeric',
                'min:100000',
                'max:' . $this->current_balance
            ]
        ], [
            'employee.required' => 'NIK gak ketemu, Bos! Cari dulu nasabahnya.',
            'amount.min' => 'Aturan Pabrik: Minimal pencairan Rp 100.000!',
            'amount.max' => 'Waduh, saldo gak cukup! Maksimal cuma Rp ' . number_format($this->current_balance, 0, ',', '.')
        ]);

        DB::beginTransaction();
        try {
            $withdrawal = Withdrawal::create([
                'employee_id' => $this->employee->id,
                'officer_id'  => auth()->id(),
                'amount'      => $this->amount,
                'status'      => 'PENDING',
                'notes'       => $this->notes,
            ]);

            DB::commit();

            // Flash session buat tombol cetak di Blade
            session()->flash('success', 'Pencairan dana ' . $this->employee->name . ' berhasil diajukan!');
            session()->flash('print_id', $withdrawal->id);

            // Reset Form tapi simpan session flash
            return redirect()->route('withdrawals.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal Simpan: ' . $e->getMessage());
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

        if ($withdrawal && $withdrawal->status === 'PENDING') {
            $withdrawal->update(['status' => 'COMPLETED']);
            session()->flash('success', 'Status transfer sudah COMPLETED!');
        }
    }
}
