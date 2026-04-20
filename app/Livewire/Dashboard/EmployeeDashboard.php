<?php

namespace App\Livewire\Dashboard;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class EmployeeDashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        // ==========================================
        // 1. JALUR KHUSUS ADMIN / PETUGAS HRGA
        // ==========================================
        if ($user->can('access-admin') || $user->can('access-petugas')) {

            // --- METRIK GLOBAL (Pake weighing_at agar akurat secara historis) ---
            $masuk_global = TransactionItem::whereHas('transaction', function ($q) {
                $q->where('status', \App\Enums\TransactionStatus::POSTED->value);
            })->sum('subtotal');
            $keluar_global = Withdrawal::whereIn('status', ['PENDING', 'COMPLETED'])->sum('amount');
            $total_uang = $masuk_global - $keluar_global;

            $total_kg = TransactionItem::whereHas('transaction', function ($q) {
                $q->where('status', \App\Enums\TransactionStatus::POSTED->value);
            })->sum('weight_kg');

            $my_masuk = TransactionItem::whereHas('transaction', function ($q) use ($user) {
                $q->where('employee_id', $user->id)->where('status', \App\Enums\TransactionStatus::POSTED->value);
            })->sum('subtotal');
            $my_keluar = Withdrawal::where('employee_id', $user->id)->whereIn('status', ['PENDING', 'COMPLETED'])->sum('amount');
            $my_balance = $my_masuk - $my_keluar;

            // --- DATA GRAFIK (TREN 6 BULAN TERAKHIR) ---
            $chartLabels = [];
            $chartData = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::today()->startOfMonth()->subMonths($i);
                $chartLabels[] = $date->translatedFormat('M Y');

                // FIX: Gunakan 'weighing_at' BUKAN 'created_at' agar data impor Januari muncul
                $chartData[] = TransactionItem::whereHas('transaction', function ($q) use ($date) {
                    $q->where('status', \App\Enums\TransactionStatus::POSTED->value)
                        ->whereYear('weighing_at', $date->year)
                        ->whereMonth('weighing_at', $date->month);
                })->sum('subtotal');
            }

            // --- LEADERBOARD REAL-TIME ---
            $leaderboard = DB::table('users')
                ->select(
                    'users.id',
                    'users.name',
                    'users.employee_code',
                    'divisions.name as division_name',
                    DB::raw('COALESCE((SELECT SUM(ti.subtotal) FROM transaction_items ti JOIN transactions t ON t.id = ti.transaction_id WHERE t.employee_id = users.id AND t.status = "'.\App\Enums\TransactionStatus::POSTED->value.'"), 0) as total_masuk'),
                    DB::raw('COALESCE((SELECT SUM(amount) FROM withdrawals WHERE employee_id = users.id AND status IN ("PENDING", "COMPLETED")), 0) as total_keluar'),
                    DB::raw('COALESCE((SELECT SUM(ti.weight_kg) FROM transaction_items ti JOIN transactions t ON t.id = ti.transaction_id WHERE t.employee_id = users.id AND t.status = "'.\App\Enums\TransactionStatus::POSTED->value.'"), 0) as total_kg')
                )
                ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('transactions')
                        ->whereColumn('transactions.employee_id', 'users.id')
                        ->where('status', \App\Enums\TransactionStatus::POSTED->value);
                })
                ->get()
                ->map(function ($item) {
                    $item->total_uang = $item->total_masuk - $item->total_keluar;

                    return $item;
                })
                ->sortByDesc('total_uang')
                ->take(5)
                ->values();

            $recent_transactions = Transaction::with(['employee.division', 'items.wasteType'])
                ->latest('weighing_at') // Urutkan berdasarkan tanggal timbang
                ->limit(5)
                ->get();

            return view('livewire.dashboard.admin-dashboard', compact(
                'total_uang',
                'total_kg',
                'my_balance',
                'leaderboard',
                'recent_transactions',
                'chartLabels',
                'chartData'
            ));
        }

        // ==========================================
        // 2. JALUR KARYAWAN BIASA (NASABAH)
        // ==========================================
        $karyawan_masuk = TransactionItem::whereHas('transaction', function ($q) use ($user) {
            $q->where('employee_id', $user->id)->where('status', \App\Enums\TransactionStatus::POSTED->value);
        })->sum('subtotal');

        $karyawan_keluar = Withdrawal::where('employee_id', $user->id)
            ->whereIn('status', ['PENDING', 'COMPLETED'])
            ->sum('amount');

        $currentBalance = $karyawan_masuk - $karyawan_keluar;

        $totalWeight = TransactionItem::whereHas('transaction', function ($q) use ($user) {
            $q->where('employee_id', $user->id)->where('status', \App\Enums\TransactionStatus::POSTED->value);
        })->sum('weight_kg');

        // LOGIKA BADGE PANGKAT
        $badge = [
            'name' => '🥉 Pemula',
            'color' => 'bg-amber-600 text-white',
        ];
        if ($totalWeight > 100) {
            $badge = ['name' => '💎 Pahlawan Hijau', 'color' => 'bg-teal-500 text-white shadow-lg shadow-teal-200'];
        } elseif ($totalWeight > 50) {
            $badge = ['name' => '🥇 Pejuang Lingkungan', 'color' => 'bg-yellow-400 text-yellow-900 shadow-md shadow-yellow-200'];
        } elseif ($totalWeight > 10) {
            $badge = ['name' => '🥈 Sahabat Bumi', 'color' => 'bg-slate-300 text-slate-800 shadow-md shadow-slate-200'];
        }

        // LOGIKA ECO-METRICS
        $treesSaved = floor($totalWeight / 10);
        $carbonSaved = $totalWeight * 2.5;

        // LEADERBOARD 1: TOP 5 BERAT (PAHLAWAN BUMI)
        $leaderboardWeight = DB::table('users')
            ->join('transactions', 'users.id', '=', 'transactions.employee_id')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
            ->where('transactions.status', \App\Enums\TransactionStatus::POSTED->value)
            ->select(
                'users.name',
                'users.employee_code',
                'divisions.name as division_name',
                DB::raw('SUM(transaction_items.weight_kg) as total_kg')
            )
            ->groupBy('users.id', 'users.name', 'users.employee_code', 'divisions.name')
            ->orderByDesc('total_kg')
            ->limit(5)
            ->get();

        // LEADERBOARD 2: TOP 5 SALDO (SULTAN SAMPAH)
        $leaderboardBalance = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'users.employee_code',
                'divisions.name as division_name',
                DB::raw('COALESCE((SELECT SUM(ti.subtotal) FROM transaction_items ti JOIN transactions t ON t.id = ti.transaction_id WHERE t.employee_id = users.id AND t.status = "'.\App\Enums\TransactionStatus::POSTED->value.'"), 0) as total_masuk'),
                DB::raw('COALESCE((SELECT SUM(amount) FROM withdrawals WHERE employee_id = users.id AND status IN ("PENDING", "COMPLETED")), 0) as total_keluar')
            )
            ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('transactions')
                    ->whereColumn('transactions.employee_id', 'users.id')
                    ->where('status', \App\Enums\TransactionStatus::POSTED->value);
            })
            ->get()
            ->map(function ($item) {
                $item->total_uang = $item->total_masuk - $item->total_keluar;
                return $item;
            })
            ->sortByDesc('total_uang')
            ->take(5)
            ->values();

        $recentTransactions = Transaction::with('items.wasteType')
            ->where('employee_id', $user->id)
            ->latest('weighing_at')
            ->limit(5)
            ->get();

        return view('livewire.dashboard.employee-dashboard', compact(
            'user',
            'currentBalance',
            'totalWeight',
            'recentTransactions',
            'leaderboardWeight',
            'leaderboardBalance',
            'badge',
            'treesSaved',
            'carbonSaved'
        ));
    }
}
