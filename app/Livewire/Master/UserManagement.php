<?php

namespace App\Livewire\Master;

use App\Exports\UsersExport;
use App\Helpers\ActivityLogger;
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
#[\Livewire\Attributes\Title('Data Karyawan')]
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

    // Admin timbang cuma boleh NAMBAH karyawan — aksi lain khusus admin.
    // Wajib dicek server-side, jangan cuma sembunyiin tombol di Blade.
    private function abortUnlessAdmin(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
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
        $this->abortUnlessAdmin();

        $this->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        try {
            Excel::import(new UsersImport, $this->file_excel->getRealPath());

            $this->file_excel = null; // Bersihkan input file setelah sukses
            $this->dispatch('file-imported');
            session()->flash('message', 'Data berhasil diimport dari Excel!');
        } catch (\Exception $e) {
            // Kasih tau error spesifiknya kalau gagal
            session()->flash('error', 'Gagal import! Pastikan nama divisi sesuai. Detail: '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        $this->abortUnlessAdmin();

        $user = \App\Models\User::findOrFail($id);

        // PROTEKSI 1: Jangan biarkan Admin hapus dirinya sendiri pas lagi login
        if ($id == auth()->id()) {
            session()->flash('error', 'Anda tidak diizinkan untuk menghapus akun Anda sendiri.');

            return;
        }

        // Kalau aman dari semua gembok di atas, baru hapus (Soft Delete)
        $user->delete();

        ActivityLogger::log('delete_user', "Menonaktifkan (Soft Delete) akun karyawan: {$user->name} (NIK: {$user->employee_code})", 'User', $id);

        session()->flash('message', 'Karyawan berhasil dihapus (disembunyikan dari sistem).');
    }

    public function resetPassword($id)
    {
        $this->abortUnlessAdmin();

        // Proteksi: Admin tidak boleh reset password dirinya sendiri lewat halaman ini
        if ($id == auth()->id()) {
            session()->flash('error', 'Gunakan halaman Profil untuk mengubah password Anda sendiri.');

            return;
        }

        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make($user->employee_code)]);

        ActivityLogger::log('reset_password', "Reset password karyawan: {$user->name} (NIK: {$user->employee_code}) ke default NIK", 'User', $user->id);

        session()->flash('message', "Password {$user->name} berhasil direset. Login default: NIK {$user->employee_code}");
    }

    public function edit($id)
    {
        $this->abortUnlessAdmin();

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
        if ($this->isEditMode) {
            $this->abortUnlessAdmin();
        }

        // Anti privilege escalation: admin timbang cuma boleh bikin nasabah biasa
        if (! auth()->user()->isAdmin()) {
            $this->role = 'karyawan';
        }

        $rules = [
            'employee_code' => ['required', Rule::unique('users')->ignore($this->userId)],
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($this->userId)],
            'division_id' => 'required|exists:divisions,id',
            'role' => 'required|in:admin,petugas,admin_timbang,karyawan',
        ];

        $this->validate($rules);

        if ($this->isEditMode) {
            $user = User::find($this->userId);
            $user->fill([
                'name' => $this->name,
                'employee_code' => $this->employee_code,
                'email' => $this->email,
                'division_id' => $this->division_id,
            ]);
            $user->role = $this->role;
            $user->is_active = $this->is_active;
            $user->save();

            $msg = 'Data karyawan berhasil diperbarui!';
            ActivityLogger::log('update_user', "Memperbarui data karyawan: {$user->name} (NIK: {$user->employee_code})", 'User', $user->id);
        } else {
            $newUser = new User;
            $newUser->fill([
                'name' => $this->name,
                'employee_code' => $this->employee_code,
                'email' => $this->email ?? $this->employee_code.'@bank.sampah',
                'password' => Hash::make($this->employee_code),
                'division_id' => $this->division_id,
            ]);
            $newUser->role = $this->role;
            $newUser->is_active = $this->is_active;
            $newUser->save();

            $msg = 'Karyawan baru berhasil didaftarkan!';
            ActivityLogger::log('create_user', "Mendaftarkan karyawan baru: {$newUser->name} (NIK: {$newUser->employee_code})", 'User', $newUser->id);
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
