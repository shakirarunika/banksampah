<?php

use App\Livewire\Dashboard\EmployeeDashboard;
use App\Livewire\Master\DivisionManagement;
use App\Livewire\Master\UserManagement;
use App\Livewire\Master\WasteManagement;
use App\Livewire\Master\ActivityLogIndex;
use App\Livewire\Transaction\TransactionCreate;
use App\Livewire\Transaction\TransactionIndex;
use App\Livewire\Transaction\WithdrawalCreate;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Kalau dia sudah login, lempar ke Dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    // Kalau belum login, lempar ke halaman Login
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // 1. DASHBOARD
    Route::get('/dashboard', EmployeeDashboard::class)->name('dashboard');

    // 2. PROFILE (WAJIB ADA INI BIAR NAVIGASI GAK ERROR)
    // Sesuaikan dengan file yang lo punya, biasanya Breeze Livewire pakai ini:
    Route::view('profile', 'profile')->name('profile');

    // 3. AKSES PETUGAS (Transaksi & Pencairan)
    Route::middleware(['can:access-petugas'])->group(function () {
        Route::get('/transaksi', TransactionIndex::class)->name('transactions.index');
        Route::get('/transaksi/buat', TransactionCreate::class)->name('transactions.create');

        Route::get('/pencairan/buat', App\Livewire\Transaction\WithdrawalCreate::class)->name('withdrawals.create');

        Route::get('/pencairan/cetak/{id}', function ($id) {
            // Ambil data tarikan uang beserta relasi nama karyawan dan petugasnya
            $withdrawal = Withdrawal::with(['employee.division', 'officer'])->findOrFail($id);

            return view('print.withdrawal', compact('withdrawal'));
        })->name('withdrawals.print')->middleware('can:access-petugas');
        Route::get('/transaksi/{transaction}/edit', \App\Livewire\Transaction\TransactionEdit::class)->name('transactions.edit');

        Route::get('/pencairan', WithdrawalCreate::class)->name('withdrawals.index');
    });

    // 4. AKSES ADMIN (Master Data)
    Route::middleware(['can:access-admin'])->group(function () {
        Route::get('/master/sampah', WasteManagement::class)->name('master.waste');
        Route::get('/master/karyawan', UserManagement::class)->name('master.users');
        Route::get('/master/divisi', DivisionManagement::class)->name('master.division');
        Route::get('/master/log-aktivitas', ActivityLogIndex::class)->name('master.activity-log');

        // Vendor Sales & Reconciliation
        Route::get('/vendor-sales', \App\Livewire\VendorSale\Index::class)->name('vendor-sales.index');
        Route::get('/vendor-sales/create', \App\Livewire\VendorSale\Form::class)->name('vendor-sales.create');
        Route::get('/vendor-sales/{id}/edit', \App\Livewire\VendorSale\Form::class)->name('vendor-sales.edit');
        Route::get('/reconciliation', \App\Livewire\VendorSale\Reconciliation::class)->name('reconciliation.index');
    });
});

require __DIR__.'/auth.php';
