<div class="py-8 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8">
            <h2 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tighter uppercase">
                Form <span class="text-red-600">Pencairan Dana</span>
            </h2>
            <p class="text-xs text-slate-500 font-bold tracking-widest uppercase mt-1">Pengajuan Transfer Koperasi Dasi
                Aya</p>
        </div>

        @if (session()->has('success'))
            <div x-data="{ show: true }" x-show="show"
                class="mb-6 p-6 bg-emerald-50 border-2 border-emerald-500 rounded-3xl shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 animate-fade-in">
                <div>
                    <h4 class="font-black text-emerald-700 text-lg uppercase tracking-tight">Transaksi Berhasil!</h4>
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest">{{ session('success') }}</p>
                </div>

                @if (session()->has('print_id'))
                    <button onclick="window.open('{{ route('withdrawals.print', session('print_id')) }}', '_blank')"
                        class="px-8 py-3 bg-emerald-600 text-white font-black rounded-xl uppercase tracking-widest text-xs hover:bg-emerald-700 transition shadow-lg shadow-emerald-200 w-full md:w-auto flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Cetak Bukti Koperasi
                    </button>
                @endif
            </div>
        @endif

        @if (session()->has('error'))
            <div
                class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 font-bold text-xs uppercase tracking-widest rounded-r-xl">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">

                <div class="space-y-6">
                    <div>
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">1.
                            Verifikasi NIK Karyawan</label>
                        <input type="text" wire:model.live="search_nik"
                            placeholder="Ketik NIK Nasabah (Contoh: 12345)..."
                            class="w-full border-2 border-slate-200 rounded-2xl p-4 text-lg font-black text-slate-800 focus:border-emerald-500 focus:ring-0 transition bg-slate-50 placeholder:text-slate-300">
                    </div>

                    @if ($employee)
                        <div
                            class="p-6 bg-emerald-50 border border-emerald-100 rounded-3xl relative overflow-hidden group">
                            <div
                                class="absolute -right-4 -top-4 w-20 h-20 bg-white rounded-full opacity-20 group-hover:scale-110 transition-transform">
                            </div>

                            <div class="relative z-10">
                                <div class="text-[9px] text-emerald-600 font-black uppercase tracking-widest mb-1">
                                    Nasabah Terdeteksi:</div>
                                <div class="text-xl font-black text-slate-800 uppercase truncate">{{ $employee->name }}
                                </div>
                                <div
                                    class="inline-block mt-1 px-2 py-0.5 bg-emerald-600 text-white text-[9px] font-black rounded uppercase">
                                    {{ $employee->division->name ?? 'UMUM' }}
                                </div>

                                <div class="mt-6 pt-4 border-t border-emerald-200/50 flex justify-between items-end">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Saldo
                                        Saat Ini:</span>
                                    <span class="text-3xl font-black text-emerald-600 tracking-tighter">
                                        Rp {{ number_format($current_balance, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-10 border-2 border-dashed border-slate-100 rounded-3xl text-center">
                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Silakan Masukkan
                                NIK Untuk Cek Saldo</p>
                        </div>
                    @endif
                </div>

                <div class="{{ !$employee ? 'opacity-20 pointer-events-none' : '' }} space-y-6">
                    <div>
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">2.
                            Nominal Pencairan (Min. 100k)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-black text-xl">Rp</span>
                            </div>
                            <input type="number" wire:model="amount" placeholder="0"
                                class="w-full pl-14 border-2 border-slate-200 rounded-2xl p-5 text-4xl font-black text-right text-red-600 focus:border-red-500 focus:ring-0 transition shadow-inner">
                        </div>
                        @error('amount')
                            <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌
                                {{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">3.
                            Catatan Transfer</label>
                        <input type="text" wire:model="notes"
                            class="w-full border-2 border-slate-100 rounded-xl p-3 text-sm font-bold text-slate-600 focus:border-red-400">
                    </div>

                    <button wire:click="saveWithdrawal" wire:loading.attr="disabled"
                        class="w-full py-5 bg-red-600 text-white font-black rounded-2xl uppercase tracking-widest text-sm hover:bg-red-700 transition shadow-xl shadow-red-100 active:scale-95 disabled:bg-slate-300">
                        <span wire:loading.remove>Konfirmasi Pencairan Dana</span>
                        <span wire:loading>Memproses Data...</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-10 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex justify-between items-center">
                <h3 class="font-black text-slate-800 text-sm tracking-tight uppercase">5 Log Pencairan Terakhir</h3>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Otoritas HRGA &
                    Koperasi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-400 font-black uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="p-6">Waktu</th>
                            <th class="p-6">Nasabah (Divisi)</th>
                            <th class="p-6 text-right">Nominal</th>
                            <th class="p-6 text-center">Status</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($recent_withdrawals as $wd)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-6 text-slate-500 font-mono text-[10px]">
                                    {{ $wd->created_at->format('d/m/y') }}<br>{{ $wd->created_at->format('H:i') }}
                                </td>
                                <td class="p-6">
                                    <div class="font-black text-slate-800 text-xs uppercase">
                                        {{ $wd->employee?->name ?? 'Terhapus' }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span
                                            class="text-[9px] font-mono font-bold text-slate-400">#{{ $wd->employee?->employee_code ?? '-' }}</span>
                                        <span
                                            class="text-[9px] font-black text-emerald-600 uppercase">{{ $wd->employee->division->name ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="p-6 text-right font-black text-red-600 text-xs">
                                    Rp {{ number_format($wd->amount, 0, ',', '.') }}
                                </td>
                                <td class="p-6 text-center">
                                    <span
                                        class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest 
                                        {{ $wd->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200' }}">
                                        {{ $wd->status }}
                                    </span>
                                </td>
                                <td class="p-6">
                                    <div class="flex justify-center gap-2">
                                        @if ($wd->status === 'PENDING')
                                            <button wire:click="completeWithdrawal({{ $wd->id }})"
                                                onclick="return confirm('Sudah transfer uangnya? Status akan jadi COMPLETED.')"
                                                class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg transition-all"
                                                title="Selesaikan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        @endif
                                        <a href="{{ route('withdrawals.print', $wd->id) }}" target="_blank"
                                            class="p-2 bg-slate-50 text-slate-500 hover:bg-slate-200 rounded-lg transition-all"
                                            title="Cetak Ulang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                                </path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-20 text-center">
                                    <div class="flex flex-col items-center opacity-20">
                                        <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                stroke-width="2"></path>
                                        </svg>
                                        <p class="text-[10px] font-black uppercase tracking-widest">Belum Ada Transaksi
                                            Keluar</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
