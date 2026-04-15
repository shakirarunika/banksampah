<?php

namespace App\Livewire\Master;

use App\Models\Division;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DivisionsExport;
use App\Imports\DivisionsImport;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class DivisionManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $name, $divisionId;
    public $isEditMode = false;
    public $search = '';
    public $file_excel;

    public function downloadTemplate()
    {
        return Excel::download(new DivisionsExport(true), 'template_divisi.xlsx');
    }

    public function exportExcel()
    {
        return Excel::download(new DivisionsExport(false), 'data_divisi_' . now()->format('d_m_Y') . '.xlsx');
    }

    public function importExcel()
    {
        $this->validate(['file_excel' => 'required|mimes:xlsx,xls']);
        try {
            Excel::import(new DivisionsImport, $this->file_excel->getRealPath());
            $this->file_excel = null;
            session()->flash('message', 'Data divisi berhasil diimport!');
        } catch (\Exception $e) {
            // GANTI BARIS INI BIAR KELIHATAN ERROR ASLINYA
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $this->validate(['name' => 'required|unique:divisions,name,' . $this->divisionId]);

        Division::updateOrCreate(
            ['id' => $this->divisionId],
            ['name' => strtoupper($this->name)]
        );

        session()->flash('message', $this->isEditMode ? 'Divisi diupdate!' : 'Divisi ditambah!');
        $this->cancelEdit();
    }

    public function delete($id)
    {
        $division = \App\Models\Division::findOrFail($id);

        if ($division->users()->count() > 0) {
            session()->flash('error', 'Gagal hapus! Masih ada ' . $division->users()->count() . ' karyawan di divisi ini.');
            return;
        }

        $division->delete();
        session()->flash('message', 'Divisi berhasil dihapus!');
    }

    public function edit($id)
    {
        $div = Division::find($id);
        $this->divisionId = $div->id;
        $this->name = $div->name;
        $this->isEditMode = true;
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'divisionId', 'isEditMode', 'file_excel']);
    }

    public function render()
    {
        return view('livewire.master.division-management', [
            'divisions' => Division::where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10)
        ]);
    }
}
