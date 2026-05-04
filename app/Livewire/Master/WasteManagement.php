<?php

namespace App\Livewire\Master;

use App\Exports\WasteTypeExport;
use App\Imports\WasteTypeImport;
use App\Models\WastePrice;
use App\Models\WasteType;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class WasteManagement extends Component
{
    use WithFileUploads, WithPagination;

    public $name;

    public $code;

    public $price;

    public $unit = 'kg';

    public $selected_waste_id;

    public $new_price;

    public $effective_date;

    public $file_excel;

    public $search = '';

    // Variabel buat nampung histori
    public $priceHistory = [];

    public $showHistoryModal = false;

    // Action Excel
    public function downloadTemplate()
    {
        return Excel::download(new WasteTypeExport(true), 'template_sampah.xlsx');
    }

    public function exportExcel()
    {
        return Excel::download(new WasteTypeExport(false), 'data_sampah_'.now()->format('d_m_Y').'.xlsx');
    }

    public function importExcel()
    {
        $this->validate(['file_excel' => 'required|mimes:xlsx,xls']);
        try {
            Excel::import(new WasteTypeImport, $this->file_excel->getRealPath());
            $this->file_excel = null;
            session()->flash('message', 'Master sampah dan harga berhasil diupdate!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengimpor data! Silakan periksa kembali format file Anda.');
        }
    }

    public function delete($id)
    {
        $waste = \App\Models\WasteType::findOrFail($id);

        if ($waste->transactions()->exists()) {
            session()->flash('error', 'GAGAL: Jenis sampah ini sudah terikat pada Riwayat Transaksi. Tidak dapat dihapus!');

            return;
        }

        try {
            // Hapus dalam urutan yang benar (child dulu, baru parent) di dalam DB transaction
            // Ini aman karena tidak perlu menonaktifkan FK constraint
            DB::transaction(function () use ($id) {
                // 1. Hapus histori harga dulu (child table)
                WastePrice::where('waste_type_id', $id)->delete();

                // 2. Hapus master sampahnya (parent table)
                WasteType::where('id', $id)->delete();
            });

            // Reset form kalau yang dihapus lagi di-klik
            if ($this->selected_waste_id == $id) {
                $this->reset(['selected_waste_id', 'new_price', 'effective_date']);
            }

            session()->flash('message', 'Master sampah dan harganya resmi dibumihanguskan!');
        } catch (\Exception $e) {
            session()->flash('error', 'Sistem Gagal Hapus: '.$e->getMessage());
        }
    }

    // Logic Manual
    public function saveWaste()
    {
        $this->validate([
            'code' => 'required|unique:waste_types,code',
            'name' => 'required',
            'price' => 'required|numeric',
        ]);

        DB::transaction(function () {
            $waste = WasteType::create([
                'code' => strtoupper($this->code),
                'name' => $this->name,
                'unit' => $this->unit,
            ]);

            WastePrice::create([
                'waste_type_id' => $waste->id,
                'user_id' => auth()->id(),
                'price_per_kg' => $this->price,
                'effective_from' => now(),
            ]);
        });

        $this->reset(['code', 'name', 'price']);
        session()->flash('message', 'Sampah baru berhasil ditambah!');
    }

    public function selectWaste($id)
    {
        $waste = WasteType::with('currentPrice')->find($id);
        $this->selected_waste_id = $id;
        $this->name = $waste->name;
        $this->new_price = $waste->currentPrice->price_per_kg ?? 0;
        $this->effective_date = now()->format('Y-m-d\TH:i');
    }

    public function viewHistory($id)
    {
        $this->selected_waste_id = $id;
        $waste = WasteType::with(['prices.admin'])->find($id);
        $this->priceHistory = $waste->prices;
        $this->name = $waste->name;
        $this->showHistoryModal = true;
    }

    public function updatePrice()
    {
        $this->validate([
            'new_price' => 'required|numeric',
            'effective_date' => 'required',
        ]);

        WastePrice::create([
            'waste_type_id' => $this->selected_waste_id,
            'user_id' => auth()->id(), // NYATET SIAPA PELAKUNYA
            'price_per_kg' => $this->new_price,
            'effective_from' => $this->effective_date,
        ]);

        $this->reset(['selected_waste_id', 'new_price', 'effective_date']);
        session()->flash('message', 'Harga berhasil diperbarui & masuk histori!');
    }

    public function render()
    {
        return view('livewire.master.waste-management', [
            'wasteTypes' => WasteType::with('currentPrice')
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('code', 'like', '%'.$this->search.'%')
                ->paginate(10),
        ]);
    }
}
