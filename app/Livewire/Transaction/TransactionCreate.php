<?php

namespace App\Livewire\Transaction;

use App\Models\User;
use App\Models\WasteType;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use App\Imports\WasteTransactionImport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.app')]
class TransactionCreate extends Component
{
    use WithFileUploads;
    public $file_import;

    public function downloadTemplate(): BinaryFileResponse
    {
        $filePath = public_path('templates/template_bank_sampah.xlsx');

        // Pastikan filenya sudah lo buat dan taruh di folder public/templates/
        return response()->download($filePath, 'Template_Impor_Bank_Sampah.xlsx');
    }

    public function importExcel()
    {
        $this->validate([
            'file_import' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new WasteTransactionImport, $this->file_import->getRealPath());

            session()->flash('message', 'Boom! Data Januari berhasil disikat masuk semua.');
            return redirect()->route('transactions.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Waduh, Excel-nya ngaco: ' . $e->getMessage());
        }
    }

    // 1. Data Karyawan & Transaksi
    public $search_nik = '';
    public $employee;
    public $transaction_date; // Tambahkan ini

    // 2. Form Input Keranjang
    public $selected_waste = '';
    public $weight = '';

    // 3. Isi Keranjang (Cart)
    public $items = [];

    // Fungsi mount buat set default tanggal ke hari ini
    public function mount()
    {
        $this->transaction_date = now()->format('Y-m-d');
    }

    public function updatedSearchNik($value)
    {
        $this->employee = User::where('employee_code', $value)
            ->where('is_active', true)
            ->first();
    }

    public function addItem()
    {
        $this->validate([
            'selected_waste' => 'required',
            'weight' => 'required|numeric|min:0.01',
        ], [
            'selected_waste.required' => 'Pilih jenis sampah dulu, Bos!',
            'weight.required' => 'Beratnya jangan kosong.',
            'weight.min' => 'Berat minimal 0.01 kg.'
        ]);

        $waste = WasteType::with('currentPrice')->find($this->selected_waste);

        if (!$waste) {
            session()->flash('error', 'Jenis sampah tidak valid.');
            return;
        }

        $price = $waste->currentPrice->price_per_kg ?? 0;
        $subtotal = $this->weight * $price;

        $this->items[] = [
            'waste_type_id' => $waste->id,
            'waste_name'    => $waste->name,
            'weight'        => (float) $this->weight,
            'price'         => $price,
            'subtotal'      => $subtotal
        ];

        $this->reset(['selected_waste', 'weight']);
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function saveTransaction()
    {
        if (!$this->employee) {
            session()->flash('error', 'Pilih nasabah (karyawan) dulu!');
            return;
        }

        if (empty($this->items)) {
            session()->flash('error', 'Keranjang timbangan masih kosong!');
            return;
        }

        // Tambahkan validasi tanggal
        $this->validate(['transaction_date' => 'required|date|before_or_equal:today']);

        DB::beginTransaction();
        try {
            // Gabungkan tanggal input dengan jam sekarang supaya urutan transaksi tetep rapi
            $weighingDateTime = Carbon::parse($this->transaction_date . ' ' . now()->format('H:i:s'));

            $transaction = Transaction::create([
                'employee_id' => $this->employee->id,
                'officer_id'  => auth()->id(),
                'weighing_at' => $weighingDateTime, // Pake tanggal pilihan user
                'status'      => 'POSTED',
            ]);

            foreach ($this->items as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'waste_type_id'  => $item['waste_type_id'],
                    'weight_kg'      => $item['weight'],
                    'price_at_time'  => $item['price'],
                    'subtotal'       => $item['subtotal'],
                ]);
            }

            DB::commit();

            session()->flash('message', 'Timbangan berhasil disimpan!');
            return redirect()->route('transactions.index');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $wasteTypes = WasteType::with('currentPrice')->get();

        return view('livewire.transaction.transaction-create', [
            'wasteTypes' => $wasteTypes
        ]);
    }
}
