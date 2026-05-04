<?php

namespace App\Livewire\Transaction;

use App\Imports\WasteTransactionImport;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Withdrawal;
use App\Services\TransactionService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.app')]
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
            session()->flash('message', 'Transaksi berhasil dibatalkan secara aman.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $transactions = Transaction::with(['employee.division', 'items.wasteType', 'officer'])
            ->whereHas('employee', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('employee_code', 'like', "%{$this->search}%");
            })
            ->latest()
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

        DB::beginTransaction();
        try {
            // Hapus detail dulu, baru induk
            \App\Models\TransactionItem::query()->delete();
            \App\Models\Transaction::query()->delete();

            DB::commit();
            session()->flash('message', 'Selesai! Semua data transaksi berhasil dihapus/direset.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal reset: '.$e->getMessage());
        }
    }
}
