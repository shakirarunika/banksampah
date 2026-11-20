<?php

namespace App\Livewire\Transaction;

use App\Livewire\Forms\TransactionForm;
use App\Models\User;
use App\Models\WasteType;
use App\Services\TransactionService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
#[\Livewire\Attributes\Title('Input Timbangan')]
class TransactionCreate extends Component
{
    protected TransactionService $transactionService;

    public function boot(TransactionService $transactionService): void
    {
        $this->transactionService = $transactionService;
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
        $waste = WasteType::find($this->form->selected_waste);

        if (! $waste) {
            session()->flash('error', 'Jenis sampah tidak valid.');

            return;
        }

        try {
            $price = $this->transactionService->priceFor($waste, $this->form->transaction_date);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->form->addItem($waste, $price);
    }

    // Tanggal timbang diganti: harga di keranjang ikut tanggal baru,
    // jenis yang gak berlaku di tanggal itu dibuang dari keranjang.
    public function updated($property)
    {
        if ($property !== 'form.transaction_date' || ! strtotime((string) $this->form->transaction_date)) {
            return;
        }

        $this->form->selected_waste = '';
        $dropped = [];
        foreach ($this->form->items as $i => $item) {
            try {
                $price = $this->transactionService->priceFor(WasteType::find($item['waste_type_id']), $this->form->transaction_date);
                $this->form->items[$i]['price'] = $price;
                $this->form->items[$i]['subtotal'] = $item['weight'] * $price;
            } catch (\Exception $e) {
                $dropped[] = $item['waste_name'];
                unset($this->form->items[$i]);
            }
        }
        $this->form->items = array_values($this->form->items);

        if ($dropped) {
            session()->flash('error', 'Dihapus dari keranjang karena tidak berlaku di tanggal ini: '.implode(', ', $dropped));
        }
    }

    public function removeItem($index)
    {
        $this->form->removeItem($index);
    }

    public function saveTransaction()
    {
        $throttleKey = 'transaction-submit-'.auth()->id();
        // env() di sini bakal null kalau config di-cache — wajib lewat config()
        $maxAttempts = (int) config('app.throttle.transaction.limit');
        $decaySeconds = (int) config('app.throttle.transaction.decay');

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            session()->flash('error', "Terlalu banyak permintaan. Silakan coba lagi dalam {$seconds} detik.");

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, $decaySeconds);

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

            $transaction = $this->transactionService->createTransaction([
                'employee_id' => $this->employee->id,
                'officer_id' => auth()->id(),
                'weighing_at' => $weighingDateTime,
            ], $this->form->items);

            session()->flash('message', 'Timbangan berhasil disimpan!');
            session()->flash('print_id', $transaction->id);

            return redirect()->route('transactions.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan data: '.$e->getMessage());
        }
    }

    public function render()
    {
        $date = strtotime((string) $this->form->transaction_date) ? $this->form->transaction_date : now();
        $wasteTypes = WasteType::withPriceOn($date, WasteType::usableOn($date));

        return view('livewire.transaction.transaction-create', [
            'wasteTypes' => $wasteTypes,
        ]);
    }
}
