<?php

namespace App\Livewire\Transaction;

use App\Imports\WasteTransactionImport;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\WasteType;
use Carbon\Carbon;
use App\Services\TransactionService;
use Illuminate\Support\Facades\DB;
use App\Livewire\Forms\TransactionForm;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.app')]
class TransactionCreate extends Component
{
    use WithFileUploads;

    protected TransactionService $transactionService;

    public function boot(TransactionService $transactionService): void
    {
        $this->transactionService = $transactionService;
    }

    public $file_import;

    public function downloadTemplate(): BinaryFileResponse
    {
        $filePath = public_path('templates/template_bank_sampah.xlsx');

        // Pastikan file template sudah disiapkan di folder public/templates/
        return response()->download($filePath, 'Template_Impor_Bank_Sampah.xlsx');
    }

    public function importExcel()
    {
        $this->validate([
            'file_import' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new WasteTransactionImport, $this->file_import->getRealPath());

            session()->flash('message', 'Sip! Data berhasil diimpor semua.');

            return redirect()->route('transactions.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Mohon maaf, terjadi kesalahan pada file Excel: '.$e->getMessage());
        }
    }

    // 1. Data Karyawan & Transaksi
    public $search_nik = '';

    public $employee;

    public TransactionForm $form;

    // Fungsi mount untuk mengatur default tanggal ke hari ini
    public function mount()
    {
        $this->form->initDefaultDate();
    }

    public function updatedSearchNik($value)
    {
        $this->employee = User::where('employee_code', $value)
            ->where('is_active', true)
            ->first();
    }

    public function addItem()
    {
        $waste = WasteType::with('currentPrice')->find($this->form->selected_waste);

        if (! $waste) {
            session()->flash('error', 'Jenis sampah tidak valid.');

            return;
        }

        $this->form->addItem($waste);
    }

    public function removeItem($index)
    {
        $this->form->removeItem($index);
    }

    public function saveTransaction()
    {
        if (! $this->employee) {
            session()->flash('error', 'Silakan pilih nasabah (karyawan) terlebih dahulu.');

            return;
        }

        if (empty($this->form->items)) {
            session()->flash('error', 'Keranjang timbangan masih kosong!');

            return;
        }

        // Jalankan validasi khusus tanggal pada form object
        $this->form->validate();

        try {
            // Gabungkan tanggal input dengan waktu saat ini agar urutan transaksi tetap rapi
            $weighingDateTime = Carbon::parse($this->form->transaction_date.' '.now()->format('H:i:s'));

            $this->transactionService->createTransaction([
                'employee_id' => $this->employee->id,
                'officer_id' => auth()->id(),
                'weighing_at' => $weighingDateTime,
            ], $this->form->items);

            session()->flash('message', 'Timbangan berhasil disimpan!');

            return redirect()->route('transactions.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan data: '.$e->getMessage());
        }
    }

    public function render()
    {
        $wasteTypes = WasteType::with('currentPrice')->get();

        return view('livewire.transaction.transaction-create', [
            'wasteTypes' => $wasteTypes,
        ]);
    }
}
