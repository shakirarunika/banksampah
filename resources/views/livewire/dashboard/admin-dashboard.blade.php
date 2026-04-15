<div class="py-8 md:py-10 bg-slate-50 min-h-screen">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-black text-slate-800 tracking-tighter uppercase">
                Admin <span class="text-emerald-600">Overview</span>
            </h1>
            <p class="text-xs text-slate-500 font-bold tracking-widest uppercase mt-1">
                Laporan Bank Sampah Dasi Aya • {{ now()->format('d M Y') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-emerald-600 text-white rounded-2xl p-6 shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Total Tabungan Global
                    </div>
                    <div class="text-3xl lg:text-4xl font-black tracking-tighter">Rp
                        {{ number_format($total_uang ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="bg-green-500 text-white rounded-2xl p-6 shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Total Sampah Tereduksi
                    </div>
                    <div class="text-3xl lg:text-4xl font-black tracking-tighter">{{ number_format($total_kg ?? 0, 2) }}
                        <span class="text-xl opacity-80">Kg</span></div>
                </div>
            </div>

            <div class="bg-teal-600 text-white rounded-2xl p-6 shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Saldo Pribadi Anda
                    </div>
                    <div class="text-3xl lg:text-4xl font-black tracking-tighter text-teal-100">Rp
                        {{ number_format($my_balance ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6" wire:ignore>
                <h3 class="font-black text-slate-800 text-base tracking-tight uppercase mb-4">Tren Setoran 6 Bulan
                    Terakhir</h3>
                <div class="relative h-64 w-full">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <div
                class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col overflow-hidden">
                <div
                    class="p-4 border-b border-amber-100 bg-gradient-to-r from-amber-50 to-white flex justify-between items-center">
                    <h3 class="text-xs font-black text-amber-600 tracking-widest uppercase flex items-center gap-2">
                        🏆 Top Pahlawan (Saldo Aktif)
                    </h3>
                </div>

                <div class="divide-y divide-slate-50 flex-grow">
                    @forelse($leaderboard ?? [] as $index => $user)
                        <div class="p-4 hover:bg-amber-50/20 transition flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs flex-shrink-0 {{ $index === 0 ? 'bg-amber-400 text-white shadow-md' : ($index === 1 ? 'bg-slate-300 text-white' : ($index === 2 ? 'bg-orange-300 text-white' : 'bg-slate-100 text-slate-400')) }}">
                                #{{ $index + 1 }}
                            </div>

                            <div class="flex-grow overflow-hidden">
                                <div class="font-black text-slate-800 text-xs uppercase truncate">{{ $user->name }}
                                </div>

                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span
                                        class="text-[8px] font-mono font-bold text-slate-400 bg-slate-100 px-1 rounded">
                                        {{ $user->employee_code }}
                                    </span>
                                    <span
                                        class="text-[8px] font-black text-slate-400 uppercase truncate tracking-tighter">
                                        {{ $user->division_name ?? 'UMUM' }}
                                    </span>
                                </div>

                                <div class="text-[9px] font-bold text-emerald-500 uppercase mt-1">
                                    {{ number_format($user->total_kg, 1) }} Kg
                                </div>
                            </div>

                            <div class="text-right flex-shrink-0">
                                <div class="font-black text-amber-600 text-xs">
                                    Rp {{ number_format($user->total_uang / 1000, 0) }}k
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400 font-bold uppercase text-[10px]">Belum ada data
                            pahlawan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-50 bg-slate-50/30 flex justify-between items-center">
                <h3 class="font-black text-slate-800 text-base tracking-tight uppercase">5 Transaksi Terakhir</h3>
                <a href="{{ route('transactions.index') }}" wire:navigate
                    class="text-[10px] font-black text-emerald-600 hover:text-emerald-800 uppercase tracking-widest bg-emerald-50 px-3 py-1.5 rounded-full">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-400 font-black uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="p-4">Tanggal Timbang</th>
                            <th class="p-4">Karyawan (Nasabah)</th>
                            <th class="p-4 text-right">Nilai Ekonomi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($recent_transactions as $trx)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 text-slate-500 font-mono text-xs">
                                    {{ $trx->weighing_at->format('d/m/Y') }}
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-slate-800 text-xs uppercase">
                                        {{ $trx->employee?->name ?? 'Terhapus' }}</div>
                                    <div class="text-[9px] text-slate-400 font-bold tracking-widest">
                                        {{ $trx->items->sum('weight_kg') }} KG •
                                        {{ $trx->employee->division->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="p-4 text-right font-black text-emerald-600 text-xs">
                                    Rp {{ number_format($trx->items->sum('subtotal'), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function renderTrendChart() {
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;

            if (window.myTrendChart) {
                window.myTrendChart.destroy();
            }

            window.myTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Total Nilai Sampah (Rp)',
                        data: @json($chartData),
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#059669',
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9',
                                borderDash: [4, 4]
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('livewire:initialized', renderTrendChart);
        document.addEventListener('livewire:navigated', renderTrendChart);
    </script>
</div>
