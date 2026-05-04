<?php

namespace App\Livewire\Master;

use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\Division;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
// --- DUA BARIS INI WAJIB ADA BIAR EXCEL JALAN ---
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class UserManagement extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $file_excel;

    public $name;

    public $employee_code;

    public $email;

    public $division_id;

    public $role = 'karyawan';

    public $is_active = true;

    public $search = '';

    public $userId;

    public $isEditMode = false;

    // Reset pagination kalau lagi cari nama
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // 1. Fungsi Download Template
    public function downloadTemplate()
    {
        return Excel::download(new UsersExport(true), 'template_karyawan.xlsx');
    }

    // 2. Fungsi Export Data
    public function exportExcel()
    {
        return Excel::download(new UsersExport(false), 'data_karyawan_tgl_'.now()->format('d_m_Y').'.xlsx');
    }

    // 3. Fungsi Import Data
    public function importExcel()
    {
        $this->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        try {
            Excel::import(new UsersImport, $this->file_excel->getRealPath());

            $this->file_excel = null; // Bersihkan input file setelah sukses
            session()->flash('message', 'Data berhasil diimport dari Excel!');
        } catch (\Exception $e) {
            // Kasih tau error spesifiknya kalau gagal
            session()->flash('error', 'Gagal import! Pastikan nama divisi sesuai. Detail: '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        $user = \App\Models\User::findOrFail($id);

        // PROTEKSI 1: Jangan biarkan Admin hapus dirinya sendiri pas lagi login
        if ($id == auth()->id()) {
            session()->flash('error', 'Anda tidak diizinkan untuk menghapus akun Anda sendiri.');

            return;
        }

        // PROTEKSI 2: Cek keterlibatan di tabel Transaksi (Sebagai Nasabah ATAU Petugas)
        // Sesuaikan nama kolom ('employee_id' / 'user_id' dan 'officer_id') dengan yang ada di database
        $isTiedToTransaction = \App\Models\Transaction::where('employee_id', $user->id)
            ->orWhere('officer_id', $user->id)
            ->exists();

        if ($isTiedToTransaction) {
            session()->flash('error', 'GAGAL: Karyawan ini tidak bisa dihapus karena datanya terikat di Riwayat Transaksi (sebagai nasabah atau petugas). Coba ubah statusnya jadi Non-Aktif saja.');

            return;
        }

        // Kalau aman dari semua gembok di atas, baru hapus
        $user->delete();
        session()->flash('message', 'Karyawan berhasil dihapus secara permanen.');
    }

    public function resetPassword($id)
    {
        // Proteksi: Admin tidak boleh reset password dirinya sendiri lewat halaman ini
        if ($id == auth()->id()) {
            session()->flash('error', 'Gunakan halaman Profil untuk mengubah password Anda sendiri.');
            return;
        }

        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make($user->employee_code)]);

        session()->flash('message', "Password {$user->name} berhasil direset. Login default: NIK {$user->employee_code}");
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->employee_code = $user->employee_code;
        $this->email = $user->email;
        $this->division_id = $user->division_id;
        $this->role = $user->role;
        $this->is_active = $user->is_active;

        $this->isEditMode = true;
    }

    public function save()
    {
        $rules = [
            'employee_code' => ['required', Rule::unique('users')->ignore($this->userId)],
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($this->userId)],
            'division_id' => 'required|exists:divisions,id',
            'role' => 'required|in:admin,petugas,karyawan',
        ];

        $this->validate($rules);

        if ($this->isEditMode) {
            $user = User::find($this->userId);
            $user->update([
                'name' => $this->name,
                'employee_code' => $this->employee_code,
                'email' => $this->email,
                'division_id' => $this->division_id,
                'role' => $this->role,
                'is_active' => $this->is_active,
            ]);
            $msg = 'Data karyawan berhasil diperbarui!';
        } else {
            User::create([
                'name' => $this->name,
                'employee_code' => $this->employee_code,
                'email' => $this->email ?? $this->employee_code.'@bank.sampah',
                'password' => Hash::make($this->employee_code),
                'division_id' => $this->division_id,
                'role' => $this->role,
                'is_active' => $this->is_active,
            ]);
            $msg = 'Karyawan baru berhasil didaftarkan!';
        }

        $this->cancelEdit();
        session()->flash('message', $msg);
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'employee_code', 'email', 'division_id', 'role', 'userId', 'isEditMode', 'file_excel']);
    }

    public function render()
    {
        return view('livewire.master.user-management', [
            'users' => User::with('division')
                ->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('employee_code', 'like', "%{$this->search}%");
                })
                ->latest()
                ->paginate(10),
            'divisions' => Division::all(),
        ]);
    }
}
