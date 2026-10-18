<?php

namespace App\Livewire\Transaction;

use App\Helpers\ActivityLogger;
use App\Imports\WasteTransactionImport;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.app')]
#[\Livewire\Attributes\Title('Data Transaksi')]
class TransactionIndex extends Component
{
    // Impor trait yang dibutuhkan
    use WithFileUploads, WithPagination;

    protected TransactionService $transactionService;

    public function boot(TransactionService $transactionService): void
    {
        $this->transactionService = $transactionService;
    }

    public $search = '';

    // Format Y-m dari input type="month"; kosong = semua bulan
    public $month = '';

    public $sortField = 'weighing_at';

    public $sortDirection = 'desc';

    public function updatingSearch(): void { $this->resetPage(); }

    public function updatingMonth(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['weighing_at', 'name', 'employee_code'], true)) {
            return;
        }

        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public $file_import;

    // Fungsi untuk mengimpor transaksi dari file Excel
    public function importExcel()
    {
        $this->validate([
            'file_import' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new WasteTransactionImport, $this->file_import->getRealPath());

            $this->reset('file_import');
            $this->dispatch('file-imported');
            session()->flash('message', 'Sip! Data historis berhasil diimpor ke dalam sistem.');
        } catch (\Exception $e) {
            session()->flash('error', 'Mohon maaf, terjadi kesalahan pada file Excel: '.$e->getMessage());
        }
    }

    // Fungsi untuk mengunduh template Excel
    public function downloadTemplate(): BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $filePath = public_path('templates/template_bank_sampah.xlsx');

        if (! file_exists($filePath)) {
            session()->flash('error', 'File template belum ada di folder public/templates/');

            return redirect()->back();
        }

        return response()->download($filePath, 'Template_Impor_Bank_Sampah.xlsx');
    }

    // Fungsi untuk membatalkan (void) transaksi
    public function voidTransaction($id)
    {
        $transaction = Transaction::with('items')->find($id);

        if (! $transaction) {
            session()->flash('error', 'Transaksi tidak ditemukan.');

            return;
        }

        try {
            $this->transactionService->voidTransaction($transaction);

            ActivityLogger::log(
                'void_transaction',
                "Membatalkan transaksi #{$transaction->id} milik {$transaction->employee?->name} senilai Rp ".number_format($transaction->items->sum('subtotal'), 0, ',', '.'),
                'Transaction',
                $transaction->id
            );

            session()->flash('message', 'Transaksi berhasil dibatalkan secara aman.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        // Properti publik bisa diubah dari browser, jadi arah sort dipaksa asc/desc
        $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        $transactions = Transaction::with(['employee.division', 'items.wasteType', 'officer'])
            ->whereHas('employee', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('employee_code', 'like', "%{$this->search}%");
            })
            ->when(preg_match('/^\d{4}-\d{2}$/', $this->month), function ($q) {
                $start = Carbon::createFromFormat('Y-m-d', $this->month.'-01')->startOfDay();
                $q->where('weighing_at', '>=', $start)->where('weighing_at', '<', $start->copy()->addMonth());
            })
            ->when(
                in_array($this->sortField, ['name', 'employee_code'], true),
                fn ($q) => $q->orderBy(
                    User::withTrashed()->select($this->sortField)->whereColumn('users.id', 'transactions.employee_id'),
                    $dir
                )->orderBy('weighing_at', 'desc'),
                fn ($q) => $q->orderBy('weighing_at', $dir)
            )
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.transaction.transaction-index', [
            'transactions' => $transactions,
        ]);
    }

    public function resetAllTransactions()
    {
        // Validasi akses: Hanya Admin yang dapat melakukan reset data
        if (! auth()->user()->can('access-admin')) {
            session()->flash('error', 'Akses ditolak! Anda bukan Admin.');

            return;
        }

        // Kalau masih ada pencairan, saldo semua karyawan bakal minus setelah reset
        if (\App\Models\Withdrawal::exists()) {
            session()->flash('error', 'Reset ditolak! Masih ada data pencairan — saldo karyawan bakal minus.');

            return;
        }

        DB::beginTransaction();
        try {
            // Soft delete induknya aja; item tetap ada biar bisa di-restore
            // (semua hitungan saldo lewat whereHas('transaction') jadi otomatis dikecualikan)
            \App\Models\Transaction::query()->delete();

            DB::commit();

            ActivityLogger::log('reset_all_transactions', 'Mereset SELURUH data transaksi dari sistem.');

            session()->flash('message', 'Selesai! Semua data transaksi berhasil dihapus/direset.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal reset: '.$e->getMessage());
        }
    }
}
