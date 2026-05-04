<div class="py-6 md:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="mb-4 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl md:text-3xl font-black text-gray-800 tracking-tight leading-tight flex flex-wrap items-center gap-3">
                    <span>Selamat Datang,<br class="block md:hidden"> {{ $user->name }}</span>
                    <span class="text-[10px] md:text-xs px-3 py-1 rounded-full uppercase tracking-widest font-black {{ $badge['color'] }} inline-block align-middle">
                        {{ $badge['name'] }}
                    </span>
                </h2>
                <p class="text-[10px] md:text-sm text-gray-500 font-bold tracking-widest uppercase mt-2">
                    NIK: {{ $user->employee_code }} | Departemen: {{ $user->division->name ?? '-' }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-8">

            <div
                class="bg-emerald-600 rounded-2xl p-6 md:p-8 text-white shadow-lg shadow-blue-200 relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-emerald-100 text-[10px] md:text-xs font-bold uppercase tracking-widest mb-1 md:mb-2">
                        Saldo Tabungan Sampah</p>
                    <h3 class="text-4xl md:text-5xl font-black tracking-tighter">Rp
                        {{ number_format($currentBalance ?? 0, 0, ',', '.') }}</h3>
                    <div
                        class="mt-4 flex items-center gap-1.5 text-[9px] md:text-xs text-emerald-100 italic font-medium">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Update otomatis tiap timbangan diposting
                    </div>
                </div>
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white opacity-10 rounded-full blur-xl"></div>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl p-6 md:p-8 shadow-sm flex items-center gap-4">
                <div class="bg-green-100 p-4 rounded-full text-green-600 flex-shrink-0">
                    <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-7l-6-2m0-2v2m0 16V5m0 16H9m3 0h3">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-400 text-[10px] md:text-xs font-bold uppercase tracking-widest mb-0 md:mb-1">
                        Total Kontribusi</p>
                    <h4 class="text-2xl md:text-4xl font-black text-gray-800 tracking-tighter">
                        {{ number_format($totalWeight ?? 0, 2) }} <span
                            class="text-sm md:text-base font-bold text-gray-400">Kg</span>
                    </h4>
                </div>
            </div>

            <!-- ECO METRICS -->
            <div class="bg-gradient-to-br from-teal-50 to-emerald-50 border border-teal-100 rounded-2xl p-6 md:p-8 shadow-sm flex flex-col justify-center">
                <p class="text-teal-700 text-[10px] md:text-xs font-black uppercase tracking-widest mb-3">Dampak Lingkungan</p>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="bg-teal-100 p-2 rounded-lg text-xl shadow-sm">🌳</div>
                        <div>
                            <div class="text-[10px] font-bold text-teal-600 uppercase tracking-widest">Pohon Diselamatkan</div>
                            <div class="text-lg font-black text-teal-800">{{ $treesSaved }} <span class="text-xs">Pohon</span></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-gray-200 p-2 rounded-lg text-xl shadow-sm">☁️</div>
                        <div>
                            <div class="text-[10px] font-bold text-gray-600 uppercase tracking-widest">Emisi CO2 Dicegah</div>
                            <div class="text-lg font-black text-gray-800">{{ number_format($carbonSaved, 1) }} <span class="text-xs">Kg</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 md:p-6 border-b border-gray-50 flex justify-between items-center">
                        <h3 class="font-black text-gray-800 tracking-tight text-base md:text-lg">Riwayat Penimbangan
                        </h3>
                        <span
                            class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full uppercase">5
                            Terakhir</span>
                    </div>

                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-[10px] text-gray-400 uppercase font-black tracking-widest">
                                <tr>
                                    <th class="p-4">Waktu</th>
                                    <th class="p-4">Detail</th>
                                    <th class="p-4 text-right">Berat</th>
                                    <th class="p-4 text-right">Nilai</th>
                                    <th class="p-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-sm">
                                @forelse($recentTransactions as $trx)
                                    @php $isCancelled = $trx->status === \App\Enums\TransactionStatus::CANCELLED; @endphp
                                    <tr class="transition {{ $isCancelled ? 'bg-red-50/40 opacity-60' : 'hover:bg-emerald-50/30' }}">
                                        <td class="p-4 text-gray-500 font-mono text-xs">
                                            {{ \Carbon\Carbon::parse($trx->weighing_at)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="p-4">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($trx->items as $item)
                                                    <span class="px-2 py-0.5 font-bold uppercase tracking-widest rounded text-[9px] border {{ $isCancelled ? 'bg-red-100 text-red-400 border-red-200 line-through' : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                                        {{ $item->wasteType?->name ?? 'Terhapus' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="p-4 text-right font-medium {{ $isCancelled ? 'text-red-400 line-through' : 'text-gray-600' }}">
                                            {{ number_format($trx->items->sum('weight_kg'), 2) }} kg</td>
                                        <td class="p-4 text-right font-black {{ $isCancelled ? 'text-red-400 line-through' : 'text-emerald-600' }}">Rp
                                            {{ number_format($trx->items->sum('subtotal'), 0, ',', '.') }}</td>
                                        <td class="p-4 text-center">
                                            @if($isCancelled)
                                                <span class="px-2 py-1 rounded-full text-[8px] font-black uppercase tracking-widest bg-red-100 text-red-600 border border-red-200">Dibatalkan</span>
                                            @else
                                                <span class="px-2 py-1 rounded-full text-[8px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200">Berhasil</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="p-10 text-center text-gray-400 italic font-medium text-xs">Belum ada
                                            transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="block md:hidden divide-y divide-gray-50">
                        @forelse($recentTransactions as $trx)
                            @php $isCancelled = $trx->status === \App\Enums\TransactionStatus::CANCELLED; @endphp
                            <div class="p-4 {{ $isCancelled ? 'bg-red-50/40 opacity-60' : '' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-[10px] text-gray-500 font-mono">{{ \Carbon\Carbon::parse($trx->weighing_at)->format('d M, H:i') }}</span>
                                    <div class="flex items-center gap-2">
                                        @if($isCancelled)
                                            <span class="px-1.5 py-0.5 rounded-full text-[7px] font-black uppercase bg-red-100 text-red-600 border border-red-200">Batal</span>
                                        @endif
                                        <span class="text-[11px] font-black {{ $isCancelled ? 'text-red-400 line-through' : 'text-emerald-600' }}">Rp
                                            {{ number_format($trx->items->sum('subtotal'), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-end">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($trx->items as $item)
                                            <span class="px-1.5 py-0.5 font-bold rounded text-[8px] uppercase {{ $isCancelled ? 'bg-red-100 text-red-400 line-through' : 'bg-gray-100 text-gray-500' }}">{{ $item->wasteType?->name }}</span>
                                        @endforeach
                                    </div>
                                    <span class="text-[10px] font-bold {{ $isCancelled ? 'text-red-400 line-through' : 'text-gray-400' }}">{{ number_format($trx->items->sum('weight_kg'), 1) }} kg</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-400 text-xs">Belum ada transaksi.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden sticky top-6">
                    <div class="p-5 bg-gradient-to-br from-emerald-600 to-emerald-700 text-white">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm text-xl">
                                💰
                            </div>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-[0.2em]">Top 5 Sultan Sampah</h3>
                                <p class="text-[9px] font-bold text-emerald-100 uppercase opacity-80">Saldo Aktif Terbanyak</p>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-50 bg-white">
                        @forelse($leaderboardBalance as $index => $hero)
                            <div
                                class="p-4 flex items-center gap-3 hover:bg-emerald-50/50 transition {{ $hero->employee_code == $user->employee_code ? 'bg-emerald-50 border-l-4 border-emerald-500' : '' }}">
                                <div
                                    class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-black flex-shrink-0
                                    {{ $index == 0
                                        ? 'bg-yellow-400 text-white shadow-sm'
                                        : ($index == 1
                                            ? 'bg-slate-300 text-white'
                                            : ($index == 2
                                                ? 'bg-orange-300 text-white'
                                                : 'bg-gray-100 text-gray-400')) }}">
                                    {{ $index + 1 }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-[11px] font-black text-gray-800 uppercase truncate leading-none mb-1">
                                        {{ $hero->name }}
                                        @if ($hero->employee_code == $user->employee_code)
                                            <span class="text-[8px] text-emerald-600">(Anda)</span>
                                        @endif
                                    </p>
                                    <p class="text-[9px] font-bold text-gray-400 tracking-tighter uppercase">NIK:
                                        {{ $hero->employee_code }}</p>
                                </div>

                                <div class="text-right flex-shrink-0">
                                    <p class="text-[11px] font-black text-emerald-600 leading-none mb-1">
                                        Rp {{ number_format($hero->total_uang, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-400 text-[10px] font-bold uppercase">Data belum tersedia</div>
                        @endforelse
                    </div>

                    <!-- LEADERBOARD BERAT -->
                    <div class="p-5 bg-gradient-to-br from-blue-600 to-blue-700 text-white border-t-4 border-white">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm text-xl">
                                ⚖️
                            </div>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-[0.2em]">Top 5 Pahlawan Bumi</h3>
                                <p class="text-[9px] font-bold text-blue-100 uppercase opacity-80">Akumulasi Berat Sampah</p>
                            </div>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-50 bg-white">
                        @forelse($leaderboardWeight as $index => $hero)
                            <div
                                class="p-4 flex items-center gap-3 hover:bg-blue-50/50 transition {{ $hero->employee_code == $user->employee_code ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                                <div
                                    class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-black flex-shrink-0
                                    {{ $index == 0
                                        ? 'bg-yellow-400 text-white shadow-sm'
                                        : ($index == 1
                                            ? 'bg-slate-300 text-white'
                                            : ($index == 2
                                                ? 'bg-orange-300 text-white'
                                                : 'bg-gray-100 text-gray-400')) }}">
                                    {{ $index + 1 }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-[11px] font-black text-gray-800 uppercase truncate leading-none mb-1">
                                        {{ $hero->name }}
                                        @if ($hero->employee_code == $user->employee_code)
                                            <span class="text-[8px] text-blue-600">(Anda)</span>
                                        @endif
                                    </p>
                                    <p class="text-[9px] font-bold text-gray-400 tracking-tighter uppercase">NIK:
                                        {{ $hero->employee_code }}</p>
                                </div>

                                <div class="text-right flex-shrink-0">
                                    <p class="text-[11px] font-black text-blue-600 leading-none mb-1">
                                        {{ number_format($hero->total_kg, 1) }} kg</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-400 text-[10px] font-bold uppercase">Data belum tersedia</div>
                        @endforelse
                    </div>

                    <div class="p-4 bg-gray-50 border-t border-gray-100">
                        <p class="text-[8px] text-center font-bold text-gray-400 uppercase tracking-widest">
                            Update: {{ now()->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
