<?php

namespace App\Livewire\Transaction;

use App\Enums\TransactionStatus;
use App\Enums\WithdrawalStatus;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
#[\Livewire\Attributes\Title('Mutasi Saldo')]
class BalanceLedger extends Component
{
    #[Locked]
    public User $user;

    // /mutasi/{user} buat petugas; /mutasi-saya (tanpa parameter) buat karyawan sendiri
    public function mount(?User $user = null): void
    {
        $this->user = $user?->exists ? $user : auth()->user();
    }

    /**
     * Gabungan setoran (POSTED) & pencairan (PENDING/COMPLETED) — rumus yang sama
     * dengan User::balance, jadi saldo akhir mutasi = saldo di sistem.
     * shortcut: semua mutasi diload sekaligus tanpa pagination, ganti ke query
     * UNION + paginate kalau satu karyawan sudah punya ribuan baris.
     */
    public function entries()
    {
        $masuk = $this->user->transactions()
            ->with('items.wasteType')
            ->where('status', TransactionStatus::POSTED->value)
            ->get()
            ->map(fn ($t) => [
                'date' => $t->weighing_at,
                'type' => 'Setoran',
                'description' => $t->items->map(fn ($i) => ($i->wasteType?->name ?? 'Sampah').' '.number_format($i->weight_kg, 2, ',', '.').' kg')->implode(', '),
                'ref' => 'BST-'.str_pad($t->id, 6, '0', STR_PAD_LEFT),
                'amount' => (float) $t->items->sum('subtotal'),
            ]);

        $keluar = $this->user->withdrawals()
            ->whereIn('status', WithdrawalStatus::deducted())
            ->get()
            ->map(fn ($w) => [
                'date' => $w->created_at,
                'type' => 'Pencairan',
                'description' => ($w->notes ?: 'Pencairan').' ('.$w->status.')',
                'ref' => 'BSW-'.str_pad($w->id, 6, '0', STR_PAD_LEFT),
                'amount' => -(float) $w->amount,
            ]);

        $saldo = 0;

        return $masuk->concat($keluar)
            ->sortBy('date')
            ->values()
            ->map(function ($row) use (&$saldo) {
                $saldo += $row['amount'];

                return $row + ['balance' => $saldo];
            })
            ->reverse()
            ->values();
    }

    public function render()
    {
        $this->user->load('division');

        return view('livewire.transaction.balance-ledger', [
            'entries' => $this->entries(),
        ]);
    }
}
