<?php

namespace App\Livewire\Transaction;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Withdrawal;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Imports\WasteTransactionImport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\DB;


#[Layout('layouts.app')]
class TransactionIndex extends Component
{
    // PANGGIL SEMUA TRAIT DI SINI
    use WithPagination, WithFileUploads;

    public $search = '';
    public $file_import; // INI PENYELAMAT DARI ERROR UNDEFINED VARIABLE

    // Fungsi Impor Excel (Otak yang tadi ilang)
    public function importExcel()
    {
        $this->validate([
            'file_import' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new WasteTransactionImport, $this->file_import->getRealPath());

            $this->reset('file_import');
            session()->flash('message', 'Boom! Data historis berhasil disulap masuk ke sistem.');
        } catch (\Exception $e) {
            session()->flash('error', 'Waduh, Excel-nya ngaco: ' . $e->getMessage());
        }
    }

    // Fungsi Download Template (Biar petugas gak tebak-tebakan)
    public function downloadTemplate(): BinaryFileResponse | \Illuminate\Http\RedirectResponse
    {
        $filePath = public_path('templates/template_bank_sampah.xlsx');

        if (!file_exists($filePath)) {
            session()->flash('error', 'File template belum ada di folder public/templates/');
            return redirect()->back();
        }

        return response()->download($filePath, 'Template_Impor_Bank_Sampah.xlsx');
    }

    // Fungsi Void (Sudah gue jaga biar tetep aman)
    public function voidTransaction($id)
    {
        $transaction = Transaction::with('items')->find($id);

        if (!$transaction || $transaction->status !== 'POSTED') {
            session()->flash('error', 'Transaksi tidak valid atau sudah dibatalkan.');
            return;
        }

        $nilai_yang_mau_dihapus = $transaction->items->sum('subtotal');

        $total_masuk = TransactionItem::whereHas('transaction', function ($q) use ($transaction) {
            $q->where('employee_id', $transaction->employee_id)
                ->where('status', 'POSTED');
        })->sum('subtotal');

        $total_keluar = Withdrawal::where('employee_id', $transaction->employee_id)
            ->whereIn('status', ['PENDING', 'COMPLETED'])
            ->sum('amount');

        $saldo_sekarang = $total_masuk - $total_keluar;

        if (($saldo_sekarang - $nilai_yang_mau_dihapus) < 0) {
            session()->flash('error', 'GAGAL VOID! Saldo akan minus jika dibatalkan!');
            return;
        }

        $transaction->update(['status' => 'CANCELLED']); // Sesuaikan statusnya
        session()->flash('message', 'Transaksi berhasil dibatalkan secara aman.');
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
            'transactions' => $transactions
        ]);
    }

    public function resetAllTransactions()
    {
        // Cek keamanan: Hanya admin yang boleh hajar!
        if (!auth()->user()->can('access-admin')) {
            session()->flash('error', 'Lo bukan Admin, jangan coba-kali main nuklir!');
            return;
        }

        DB::beginTransaction();
        try {
            // Hapus detail dulu, baru induk
            \App\Models\TransactionItem::query()->delete();
            \App\Models\Transaction::query()->delete();

            DB::commit();
            session()->flash('message', 'Bersih! Semua data transaksi sudah hangus.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal reset: ' . $e->getMessage());
        }
    }
}
