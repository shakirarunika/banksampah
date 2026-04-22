<div class="py-6 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="p-4 bg-emerald-100 text-emerald-700 rounded-2xl shadow-sm border-l-4 border-emerald-500 font-bold text-sm flex justify-between items-center">
                <span class="uppercase tracking-widest text-[10px]">{{ session('message') }}</span>
                <button @click="show = false" class="font-black hover:text-emerald-900 transition text-lg">×</button>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">
                    Riwayat <span class="text-orange-600">Penjualan Vendor</span>
                </h2>
                <p class="text-[10px] text-slate-500 font-bold tracking-widest uppercase mt-1">
                    Kelola dan pantau seluruh transaksi penjualan sampah ke vendor pengangkutan
                </p>
            </div>
            <a href="{{ route('vendor-sales.create') }}" wire:navigate
                class="w-full md:w-auto bg-orange-600 text-white px-8 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-orange-700 transition shadow-lg shadow-orange-100 text-center">
                + Input Penjualan Baru
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="relative w-full md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <input type="text" wire:model.live="search" placeholder="Cari nama vendor atau jenis sampah..."
                        class="w-full pl-10 pr-4 py-3 bg-white border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 font-black uppercase text-[10px] tracking-[0.2em] border-b border-slate-100">
                            <th class="p-6">Tanggal Penjualan</th>
                            <th class="p-6">Vendor Pengangkut</th>
                            <th class="p-6">Kategori Sampah</th>
                            <th class="p-6 text-right">Berat (Kg)</th>
                            <th class="p-6 text-right">Total Harga (Rp)</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($sales as $sale)
                            <tr class="hover:bg-orange-50/20 transition-all">
                                <td class="p-6">
                                    <div class="text-[11px] font-black text-slate-800">{{ $sale->transaction_date->format('d M Y') }}</div>
                                </td>
                                <td class="p-6">
                                    <div class="font-black text-slate-800 uppercase text-xs">{{ $sale->vendor_name }}</div>
                                </td>
                                <td class="p-6">
                                    <span class="px-3 py-1 bg-slate-100 border border-slate-200 text-slate-600 rounded text-[9px] font-black uppercase tracking-tighter">
                                        {{ $sale->wasteType->name }}
                                    </span>
                                </td>
                                <td class="p-6 text-right font-black text-slate-600 text-xs">
                                    {{ number_format($sale->weight_kg, 2, ',', '.') }} <span class="text-[9px] opacity-60">kg</span>
                                </td>
                                <td class="p-6 text-right">
                                    <div class="text-xs font-black text-orange-600">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</div>
                                </td>
                                <td class="p-6">
                                    <div class="flex justify-center items-center gap-2">
                                        <a href="{{ route('vendor-sales.edit', $sale->id) }}" wire:navigate
                                            class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" stroke-width="2" stroke-linecap="round" />
                                            </svg>
                                        </a>
                                        <button wire:click="delete({{ $sale->id }})" wire:confirm="Yakin ingin menghapus data ini?"
                                            class="p-2 text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2" stroke-linecap="round" />
                                        </svg>
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Belum ada data penjualan ke vendor</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-slate-50 bg-slate-50/30">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</div>
