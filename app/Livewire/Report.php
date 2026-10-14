<?php

namespace App\Livewire;

use App\Enums\TransactionStatus;
use App\Enums\WithdrawalStatus;
use App\Exports\ReportExport;
use App\Models\Division;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
#[\Livewire\Attributes\Title('Laporan')]
class Report extends Component
{
    public $date_from;

    public $date_to;

    public $division_id = '';

    public function mount()
    {
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
    }

    /** Input tanggal ngaco (diketik manual / di-tamper) jatuh ke default bulan ini. */
    private function period(): array
    {
        return [
            rescue(fn () => Carbon::parse($this->date_from)->startOfDay(), now()->startOfMonth(), false),
            rescue(fn () => Carbon::parse($this->date_to)->endOfDay(), now()->endOfDay(), false),
        ];
    }

    /**
     * Rekap per karyawan dalam periode: setoran (POSTED, by weighing_at)
     * dan pencairan (PENDING/COMPLETED, by created_at). Karyawan tanpa
     * aktivitas di periode itu gak ditampilkan.
     */
    public function rows()
    {
        [$from, $to] = $this->period();

        $setoran = fn (string $column) => TransactionItem::selectRaw("COALESCE(SUM(transaction_items.$column), 0)")
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereColumn('transactions.employee_id', 'users.id')
            ->whereNull('transactions.deleted_at')
            ->where('transactions.status', TransactionStatus::POSTED->value)
            ->whereBetween('transactions.weighing_at', [$from, $to]);

        return User::with('division')
            ->when($this->division_id, fn ($q) => $q->where('division_id', $this->division_id))
            ->select('users.*')
            ->addSelect([
                'setoran_kg' => $setoran('weight_kg'),
                'setoran_rp' => $setoran('subtotal'),
                'pencairan_rp' => Withdrawal::selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('employee_id', 'users.id')
                    ->whereIn('status', WithdrawalStatus::deducted())
                    ->whereBetween('created_at', [$from, $to]),
            ])
            ->orderBy('name')
            ->get()
            ->filter(fn ($u) => $u->setoran_kg > 0 || $u->pencairan_rp > 0)
            ->values();
    }

    public function exportExcel()
    {
        [$from, $to] = $this->period();

        return Excel::download(
            new ReportExport($this->rows()),
            'laporan_'.$from->format('Y-m-d').'_sd_'.$to->format('Y-m-d').'.xlsx'
        );
    }

    public function render()
    {
        $rows = $this->rows();
        [$from, $to] = $this->period();

        return view('livewire.report', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'divisions' => Division::orderBy('name')->get(),
            'byDivision' => $rows->groupBy(fn ($u) => $u->division->name ?? 'NON-DIVISI')
                ->map(fn ($g) => [
                    'karyawan' => $g->count(),
                    'setoran_kg' => $g->sum('setoran_kg'),
                    'setoran_rp' => $g->sum('setoran_rp'),
                    'pencairan_rp' => $g->sum('pencairan_rp'),
                ])
                ->sortKeys(),
        ]);
    }
}
