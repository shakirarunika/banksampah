<div class="py-6 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="p-4 bg-emerald-100 text-emerald-700 rounded-2xl shadow-sm border-l-4 border-emerald-500 font-bold text-sm flex justify-between items-center">
                <span class="uppercase tracking-widest text-[10px]">{{ session('message') }}</span>
                <button @click="show = false" class="font-black hover:text-emerald-900 transition text-lg">×</button>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="p-4 bg-red-100 text-red-700 rounded-2xl shadow-sm border-l-4 border-red-500 font-bold text-sm flex justify-between items-center">
                <span class="uppercase tracking-widest text-[10px]">{{ session('error') }}</span>
                <button @click="show = false" class="font-black hover:text-red-900 transition text-lg">×</button>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">
                    Riwayat <span class="text-emerald-600">Penimbangan</span>
                </h2>
                <p class="text-[10px] text-slate-500 font-bold tracking-widest uppercase mt-1">
                    Kelola dan pantau seluruh transaksi masuk sampah pabrik Dasi Aya
                </p>
            </div>
            <a href="{{ route('transactions.create') }}" wire:navigate
                class="w-full md:w-auto bg-emerald-600 text-white px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 text-center">
                + Input Timbangan Baru
            </a>
        </div>

        <div
            class="bg-white p-8 border-2 border-dashed border-emerald-200 rounded-3xl shadow-sm relative overflow-hidden group">
            <div
                class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-110 transition-transform">
            </div>

            <div class="relative z-10 flex flex-col lg:flex-row justify-between items-center gap-8">
                <div class="text-center lg:text-left">
                    <h4
                        class="text-sm font-black text-emerald-800 uppercase tracking-widest flex items-center justify-center lg:justify-start gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Impor Data Masal (Excel)
                    </h4>
                    <p class="text-[10px] font-bold text-emerald-500 uppercase mt-1">Migrasi data historis Januari -
                        Maret secara otomatis</p>
                    <a href="#" wire:click.prevent="downloadTemplate"
                        class="inline-block mt-3 text-[9px] font-black text-emerald-700 underline hover:text-emerald-900 uppercase tracking-[0.2em]">
                        📥 Unduh Template Format Excel
                    </a>
                </div>

                <form wire:submit="importExcel" class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto">
                    <div class="relative w-full sm:w-64">
                        <input type="file" wire:model="file_import" id="file_import" class="hidden" />
                        <label for="file_import"
                            class="flex items-center justify-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-white transition-all shadow-sm">
                            <span class="text-[10px] font-black text-slate-500 uppercase truncate">
                                {{ $file_import ? $file_import->getClientOriginalName() : 'Pilih File Excel...' }}
                            </span>
                        </label>
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full sm:w-auto bg-emerald-800 text-white px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-emerald-200 disabled:opacity-50 transition-all">
                        <span wire:loading.remove wire:target="importExcel">Proses Impor</span>
                        <span wire:loading wire:target="importExcel" class="flex items-center gap-2">
                            <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </button>
                </form>
            </div>

        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div
                class="p-6 border-b border-slate-50 bg-slate-50/30 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="relative w-full md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2"
                                stroke-linecap="round" />
                        </svg>
                    </div>
                    <input type="text" wire:model.live="search" placeholder="Cari NIK atau Nama Karyawan..."
                        class="w-full pl-10 pr-4 py-3 bg-white border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                </div>

                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Total: {{ $transactions->total() }} Transaksi
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50/80 text-slate-500 font-black uppercase text-[10px] tracking-[0.2em] border-b border-slate-100">
                            <th class="p-6">Waktu Penimbangan</th>
                            <th class="p-6">Karyawan (Nasabah)</th>
                            <th class="p-6">Rincian Sampah</th>
                            <th class="p-6 text-right">Berat (Kg)</th>
                            <th class="p-6 text-right">Nilai (Rp)</th>
                            <th class="p-6 text-center">Status</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($transactions as $trx)
                            <tr wire:key="trx-{{ $trx->id }}"
                                class="hover:bg-emerald-50/20 transition-all {{ $trx->status === 'CANCELLED' ? 'bg-red-50/30 opacity-60' : '' }}">
                                <td class="p-6">
                                    <div class="text-[11px] font-black text-slate-800">
                                        {{ $trx->weighing_at->format('d M Y') }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 font-mono mt-0.5">
                                        {{ $trx->created_at->format('H:i') }}</div>
                                </td>

                                <td class="p-6">
                                    <div class="font-black text-slate-800 uppercase text-xs">
                                        {{ $trx->employee?->name ?? 'User Terhapus' }}</div>
                                    <div class="text-[10px] font-bold text-emerald-600 font-mono mt-0.5">NIK:
                                        {{ $trx->employee?->employee_code ?? 'N/A' }}</div>
                                </td>

                                <td class="p-6">
                                    <div class="flex flex-wrap gap-1.5 max-w-[200px]">
                                        @foreach ($trx->items as $item)
                                            <span
                                                class="px-2 py-0.5 bg-white border border-slate-200 text-slate-600 rounded text-[9px] font-black uppercase tracking-tighter">
                                                {{ $item->wasteType?->name ?? 'Sampah' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="p-6 text-right font-black text-slate-600 text-xs">
                                    {{ number_format($trx->items->sum('weight_kg'), 2) }} <span
                                        class="text-[9px] opacity-60">kg</span>
                                </td>

                                <td class="p-6 text-right">
                                    <div class="text-xs font-black text-emerald-600">Rp
                                        {{ number_format($trx->items->sum('subtotal'), 0, ',', '.') }}</div>
                                </td>

                                <td class="p-6 text-center">
                                    <span
                                        class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border
                                        {{ $trx->status === 'POSTED'
                                            ? 'bg-emerald-100 text-emerald-700 border-emerald-200'
                                            : ($trx->status === 'CANCELLED'
                                                ? 'bg-red-100 text-red-700 border-red-200'
                                                : 'bg-amber-100 text-amber-700 border-amber-200') }}">
                                        {{ $trx->status }}
                                    </span>
                                </td>

                                <td class="p-6">
                                    <div class="flex justify-center items-center gap-2">
                                        @if ($trx->status !== 'CANCELLED')
                                            <a href="{{ route('transactions.edit', $trx->id) }}" wire:navigate
                                                class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                                                title="Edit Transaksi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                                        stroke-width="2" stroke-linecap="round" />
                                                </svg>
                                            </a>

                                            @can('access-admin')
                                                <button
                                                    wire:click="voidTransaction({{ $trx->id }})"
                                                    wire:confirm="Batalkan transaksi ini? Saldo karyawan akan otomatis dikurangi."
                                                    class="p-2 text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Void Transaksi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                                                            stroke-width="2" stroke-linecap="round" />
                                                    </svg>
                                                </button>
                                            @endcan
                                        @else
                                            <span
                                                class="text-[9px] font-black text-slate-300 uppercase italic">Voided</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-slate-200 mb-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                                stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Tidak
                                            ada transaksi ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-slate-50 bg-slate-50/30">
                {{ $transactions->links() }}
            </div>
        </div>
        @can('access-admin')
            <div class="mt-4 text-right">
                <button
                    onclick="confirm('YAKIN MAU HAPUS SEMUA DATA? Aksi ini permanen dan akan menghanguskan semua saldo karyawan!') || event.stopImmediatePropagation()"
                    wire:click="resetAllTransactions"
                    class="text-[10px] font-black text-red-400 hover:text-red-600 uppercase tracking-widest transition">
                    ⚠️ Reset Seluruh Data Transaksi
                </button>
            </div>
        @endcan
    </div>
</div>
