<?php

use App\Http\Controllers\PrintController;
use App\Livewire\Dashboard\EmployeeDashboard;
use App\Livewire\Master\ActivityLogIndex;
use App\Livewire\Master\DivisionManagement;
use App\Livewire\Master\UserManagement;
use App\Livewire\Master\WasteManagement;
use App\Livewire\Report;
use App\Livewire\Transaction\BalanceLedger;
use App\Livewire\Transaction\TransactionCreate;
use App\Livewire\Transaction\TransactionIndex;
use App\Livewire\Transaction\WithdrawalCreate;
use App\Livewire\Transaction\WithdrawalRequest;
use Illuminate\Support\Facades\Route;

// Tamu otomatis dilempar ke login oleh middleware auth di /dashboard
Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {

    // 1. DASHBOARD
    Route::get('/dashboard', EmployeeDashboard::class)->name('dashboard');

    // 2. PROFILE (WAJIB ADA INI BIAR NAVIGASI GAK ERROR)
    // Sesuaikan dengan file yang lo punya, biasanya Breeze Livewire pakai ini:
    Route::view('profile', 'profile')->name('profile');

    // Buat semua karyawan: riwayat saldo sendiri & pengajuan pencairan
    Route::get('/mutasi-saya', BalanceLedger::class)->name('ledger.mine');
    Route::get('/ajukan-pencairan', WithdrawalRequest::class)->name('withdrawals.request');

    // 3. AKSES PETUGAS (Transaksi & Pencairan)
    Route::middleware(['can:access-petugas'])->group(function () {
        Route::get('/transaksi', TransactionIndex::class)->name('transactions.index');
        Route::get('/transaksi/buat', TransactionCreate::class)->name('transactions.create');

        Route::get('/pencairan/buat', App\Livewire\Transaction\WithdrawalCreate::class)->name('withdrawals.create');

        Route::get('/pencairan/cetak/{withdrawal}', [PrintController::class, 'withdrawal'])->name('withdrawals.print');
        Route::get('/transaksi/{transaction}/edit', \App\Livewire\Transaction\TransactionEdit::class)->name('transactions.edit');

        Route::get('/transaksi/cetak/{transaction}', [PrintController::class, 'transaction'])->name('transactions.print');

        Route::get('/mutasi/{user}', BalanceLedger::class)->name('ledger.show');

        Route::get('/pencairan', WithdrawalCreate::class)->name('withdrawals.index');
    });

    // Data Karyawan: admin + admin timbang
    Route::get('/master/karyawan', UserManagement::class)
        ->middleware('can:manage-users')
        ->name('master.users');

    // 4. AKSES ADMIN (Master Data)
    Route::middleware(['can:access-admin'])->group(function () {
        Route::get('/master/sampah', WasteManagement::class)->name('master.waste');
        Route::get('/master/divisi', DivisionManagement::class)->name('master.division');
        Route::get('/master/log-aktivitas', ActivityLogIndex::class)->name('master.activity-log');

        // Vendor Sales & Reconciliation
        Route::get('/vendor-sales', \App\Livewire\VendorSale\Index::class)->name('vendor-sales.index');
        Route::get('/vendor-sales/create', \App\Livewire\VendorSale\Form::class)->name('vendor-sales.create');
        Route::get('/vendor-sales/{id}/edit', \App\Livewire\VendorSale\Form::class)->name('vendor-sales.edit');
        Route::get('/reconciliation', \App\Livewire\VendorSale\Reconciliation::class)->name('reconciliation.index');
        Route::get('/laporan', Report::class)->name('reports.index');
    });
});

require __DIR__.'/auth.php';
