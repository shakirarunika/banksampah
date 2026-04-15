<div class="py-12" x-data="{ showPriceModal: false }">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h2 class="text-2xl font-black text-gray-800 italic tracking-tighter uppercase">Form Penimbangan</h2>
            <div class="flex gap-2">
                <button @click="showPriceModal = true" type="button"
                    class="px-4 py-2 bg-slate-800 text-white font-bold rounded-md text-xs uppercase tracking-widest hover:bg-black transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Cek Harga
                </button>
                <a href="{{ route('transactions.index') }}" wire:navigate
                    class="px-4 py-2 bg-gray-200 text-gray-600 font-bold rounded-md text-xs uppercase hover:bg-gray-300 transition">Batal</a>
            </div>
        </div>

        @if (session()->has('error'))
            <div
                class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 shadow-sm flex justify-between items-center rounded">
                <span class="font-bold text-xs uppercase tracking-widest">{{ session('error') }}</span>
                <button type="button" class="font-black" onclick="this.parentElement.remove()">×</button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-1 space-y-6">
                <div class="mb-4 space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-emerald-600 ml-1">Tanggal
                        Penimbangan</label>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-emerald-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <input wire:model.live="transaction_date" type="date"
                            class="block w-full pl-10 pr-4 py-3 bg-white border-emerald-100 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-bold text-slate-700 transition-all text-sm shadow-sm" />
                    </div>
                </div>
                <div class="bg-emerald-50/50 p-6 rounded-xl border border-emerald-100 shadow-sm">
                    <label class="text-[10px] font-black text-blue-800 uppercase tracking-widest mb-2 block">1. Input
                        NIK Karyawan</label>
                    <input type="text" wire:model.live="search_nik" placeholder="Ketik/Scan NIK..."
                        class="w-full border-2 border-blue-200 rounded-lg p-3 text-lg font-black text-gray-800 focus:border-emerald-600 focus:ring-0 transition bg-white shadow-inner">

                    @if ($employee)
                        <div class="mt-4 p-4 bg-white rounded-lg border border-green-200 shadow-sm text-center">
                            <div class="text-[9px] text-green-500 font-black uppercase tracking-widest mb-1">Nasabah:
                            </div>
                            <div class="text-lg font-black text-gray-800 uppercase tracking-tight">{{ $employee->name }}
                            </div>
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                                {{ $employee->division->name ?? 'TANPA DIVISI' }}</div>
                        </div>
                    @else
                        <div class="mt-4 p-4 bg-white/50 rounded-lg border border-dashed border-gray-300 text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Menunggu
                                Nasabah...</span>
                        </div>
                    @endif
                </div>

                <div
                    class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm {{ !$employee ? 'opacity-50 pointer-events-none' : '' }}">
                    <label class="text-[10px] font-black text-gray-600 uppercase tracking-widest mb-2 block">2. Timbang
                        Sampah</label>

                    <div class="space-y-4 mt-4">
                        <div>
                            <select wire:model="selected_waste"
                                class="w-full border-gray-300 rounded-md text-xs font-bold uppercase focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">-- PILIH JENIS SAMPAH --</option>
                                @foreach ($wasteTypes as $wt)
                                    <option value="{{ $wt->id }}">{{ $wt->name }} (Rp
                                        {{ number_format($wt->currentPrice->price_per_kg ?? 0) }}/Kg)</option>
                                @endforeach
                            </select>
                            @error('selected_waste')
                                <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <div class="relative">
                                <input type="number" step="0.01" wire:model="weight" placeholder="0.00"
                                    class="w-full border-gray-300 rounded-md text-2xl font-black text-right text-emerald-600 focus:border-emerald-500 pr-12">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    <span class="text-gray-400 font-bold text-sm">KG</span>
                                </div>
                            </div>
                            @error('weight')
                                <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span>
                            @enderror
                        </div>

                        <button wire:click="addItem"
                            class="w-full py-3 bg-emerald-600 text-white font-black rounded-lg uppercase tracking-widest text-xs hover:bg-blue-700 transition shadow-md shadow-blue-200">
                            + Masukkan Keranjang
                        </button>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm h-full flex flex-col relative">

                    <h3
                        class="font-black text-gray-700 uppercase tracking-tighter border-b pb-3 mb-4 text-sm flex items-center justify-between">
                        <span>🛒 Keranjang Timbangan</span>
                        @if (count($items ?? []) > 0)
                            <span
                                class="text-[10px] text-gray-400 font-bold bg-gray-100 px-2 py-1 rounded">{{ count($items) }}
                                Item</span>
                        @endif
                    </h3>

                    <div class="flex-grow overflow-y-auto max-h-[400px]">
                        <table class="w-full text-sm text-left">
                            <thead class="sticky top-0 bg-white shadow-sm">
                                <tr class="text-gray-400 font-black uppercase text-[10px] tracking-widest border-b">
                                    <th class="py-2">Jenis Sampah</th>
                                    <th class="py-2 text-right">Berat (Kg)</th>
                                    <th class="py-2 text-right">Harga</th>
                                    <th class="py-2 text-right">Subtotal</th>
                                    <th class="py-2 text-center">Batal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($items ?? [] as $index => $item)
                                    <tr class="hover:bg-gray-50 transition" wire:key="cart-{{ $index }}">
                                        <td class="py-3 font-bold text-gray-800 text-xs">{{ $item['waste_name'] }}</td>
                                        <td class="py-3 text-right font-medium text-emerald-600">
                                            {{ number_format($item['weight'], 2) }}</td>
                                        <td class="py-3 text-right text-gray-500 text-xs">Rp
                                            {{ number_format($item['price']) }}</td>
                                        <td class="py-3 text-right font-black text-gray-800">Rp
                                            {{ number_format($item['subtotal']) }}</td>
                                        <td class="py-3 text-center">
                                            <button wire:click="removeItem({{ $index }})"
                                                class="text-red-400 hover:text-red-600 font-black text-lg leading-none">&times;</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5"
                                            class="py-12 text-center text-gray-300 font-bold italic uppercase tracking-widest text-xs">
                                            Keranjang masih kosong
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pt-4 border-t-2 border-dashed border-gray-200">
                        <div class="flex justify-between items-end mb-4">
                            <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Saldo
                                Diterima:</span>
                            <span class="text-3xl font-black text-green-600 tracking-tighter">
                                Rp {{ number_format(collect($items ?? [])->sum('subtotal')) }}
                            </span>
                        </div>

                        <button wire:click="saveTransaction"
                            class="w-full py-4 bg-green-600 text-white font-black rounded-lg uppercase tracking-widest text-sm hover:bg-green-700 transition shadow-lg shadow-green-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ count($items ?? []) == 0 || !$employee ? 'disabled' : '' }}>
                            SIMPAN TRANSAKSI
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div x-show="showPriceModal" style="display: none;" class="relative z-50" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div x-show="showPriceModal" x-transition.opacity
                class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showPriceModal" @click.away="showPriceModal = false"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                        <div
                            class="bg-slate-800 px-4 py-3 border-b border-slate-700 flex justify-between items-center">
                            <h3 class="text-sm font-black uppercase tracking-widest text-white" id="modal-title">
                                Daftar Harga Hari Ini</h3>
                            <button @click="showPriceModal = false"
                                class="text-gray-400 hover:text-white font-black text-xl leading-none">&times;</button>
                        </div>

                        <div class="bg-gray-50 px-4 pb-4 pt-5 sm:p-6 sm:pb-4 max-h-[60vh] overflow-y-auto">
                            <table class="w-full text-sm text-left">
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($wasteTypes as $wt)
                                        <tr class="hover:bg-emerald-50/50">
                                            <td class="py-2 font-bold text-gray-700 uppercase text-xs">
                                                {{ $wt->name }}</td>
                                            <td class="py-2 text-right font-black text-emerald-600">Rp
                                                {{ number_format($wt->currentPrice->price_per_kg ?? 0) }} <span
                                                    class="text-[9px] text-gray-400">/Kg</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-gray-100 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button @click="showPriceModal = false" type="button"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto uppercase tracking-widest">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
