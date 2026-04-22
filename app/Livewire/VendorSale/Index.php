<?php

namespace App\Livewire\VendorSale;

use App\Models\VendorSale;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        VendorSale::findOrFail($id)->delete();
        session()->flash('message', 'Data penjualan berhasil dihapus!');
    }

    public function render()
    {
        $sales = VendorSale::with('wasteType')
            ->where('vendor_name', 'like', '%' . $this->search . '%')
            ->orWhereHas('wasteType', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->latest('transaction_date')
            ->paginate(10);

        return view('livewire.vendor-sale.index', compact('sales'))
            ->layout('layouts.app');
    }
}
