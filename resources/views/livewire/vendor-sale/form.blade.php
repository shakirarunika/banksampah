<div class="py-8 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8 flex justify-between items-end">
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tighter uppercase">
                    Form <span class="text-orange-600">{{ $vendorSaleId ? 'Edit Penjualan' : 'Penjualan Vendor' }}</span>
                </h2>
                <p class="text-xs text-slate-500 font-bold tracking-widest uppercase mt-1">Input Data Penjualan Sampah ke Vendor / Pengepul</p>
            </div>
            <a href="{{ route('vendor-sales.index') }}" wire:navigate class="text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition">
                &larr; Kembali
            </a>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm">
            <form wire:submit.prevent="save">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-8">
                    <!-- Informasi Utama Transaksi -->
                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">1. Tanggal Transaksi</label>
                            <input type="date" wire:model="transaction_date" class="w-full border-2 border-slate-200 rounded-2xl p-4 text-lg font-black text-slate-800 focus:border-orange-500 focus:ring-0 transition bg-slate-50">
                            @error('transaction_date') <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌ {{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">2. Nama Vendor / Pengepul</label>
                            <input type="text" wire:model="vendor_name" placeholder="Misal: Pengepul Budi" class="w-full border-2 border-slate-200 rounded-2xl p-4 text-lg font-black text-slate-800 focus:border-orange-500 focus:ring-0 transition bg-slate-50 placeholder:text-slate-300">
                            @error('vendor_name') <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌ {{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">3. Foto Resi / Bukti Timbang (Opsional)</label>
                            <input type="file" wire:model="receipt_photo" class="w-full border-2 border-dashed border-slate-200 rounded-2xl p-4 text-sm font-bold text-slate-500 focus:border-orange-500 focus:ring-0 transition bg-slate-50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200" />
                            <div wire:loading wire:target="receipt_photo" class="text-[10px] font-black text-orange-500 mt-2 uppercase tracking-widest animate-pulse">Mengunggah Foto...</div>
                            @error('receipt_photo') <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌ {{ $message }}</span> @enderror
                            
                            @if ($receipt_photo)
                                <div class="mt-4 p-2 border-2 border-dashed border-orange-200 rounded-2xl text-center">
                                    <span class="block text-[9px] font-black text-orange-400 mb-2 uppercase tracking-widest">Preview Baru:</span>
                                    <img src="{{ $receipt_photo->temporaryUrl() }}" class="h-24 object-contain mx-auto rounded-xl">
                                </div>
                            @elseif ($existing_photo)
                                <div class="mt-4 p-2 border-2 border-dashed border-slate-200 rounded-2xl text-center">
                                    <span class="block text-[9px] font-black text-slate-400 mb-2 uppercase tracking-widest">Foto Saat Ini:</span>
                                    <img src="{{ asset('storage/' . $existing_photo) }}" class="h-24 object-contain mx-auto rounded-xl">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Keranjang Item Penjualan -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest font-mono">4. Rincian Sampah (Keranjang)</label>
                        <button type="button" wire:click="addItem" class="px-4 py-2 bg-orange-100 text-orange-700 hover:bg-orange-200 rounded-xl font-black text-[10px] uppercase tracking-widest transition">
                            + Tambah Kategori
                        </button>
                    </div>

                    <div class="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="hidden md:grid grid-cols-12 gap-4 p-4 bg-slate-100 border-b border-slate-200 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            <div class="col-span-4">Kategori Sampah</div>
                            <div class="col-span-3">Berat (Kg)</div>
                            <div class="col-span-4">Harga Terjual (Rp)</div>
                            <div class="col-span-1 text-center">Aksi</div>
                        </div>

                        <div class="p-4 space-y-4 md:space-y-0">
                            @foreach ($items as $index => $item)
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center {{ $loop->index > 0 ? 'pt-4 border-t border-slate-200 md:border-t-0 md:pt-0' : '' }}">
                                    <!-- Mobile Labels (Only visible on small screens) -->
                                    
                                    <div class="col-span-1 md:col-span-4">
                                        <label class="md:hidden text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Kategori Sampah</label>
                                        <select wire:model="items.{{ $index }}.waste_type_id" class="w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-800 focus:border-orange-500 focus:ring-0 transition bg-white">
                                            <option value="">-- Pilih --</option>
                                            @foreach($wasteTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("items.{$index}.waste_type_id") <span class="text-red-500 text-[9px] font-bold block mt-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-1 md:col-span-3">
                                        <label class="md:hidden text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Berat (Kg)</label>
                                        <div class="relative">
                                            <input type="number" step="0.01" wire:model.live.debounce.500ms="items.{{ $index }}.weight_kg" class="w-full pr-10 border-2 border-slate-200 rounded-xl p-3 text-sm font-black text-right text-slate-800 focus:border-orange-500 focus:ring-0 transition placeholder:text-slate-300" placeholder="0.00">
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <span class="text-slate-400 font-black text-xs">Kg</span>
                                            </div>
                                        </div>
                                        @error("items.{$index}.weight_kg") <span class="text-red-500 text-[9px] font-bold block mt-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-1 md:col-span-4">
                                        <label class="md:hidden text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Harga Terjual</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-slate-400 font-black text-xs">Rp</span>
                                            </div>
                                            <input type="number" wire:model.live.debounce.500ms="items.{{ $index }}.total_price" class="w-full pl-10 border-2 border-slate-200 rounded-xl p-3 text-sm font-black text-right text-orange-600 focus:border-orange-500 focus:ring-0 transition placeholder:text-slate-300" placeholder="0">
                                        </div>
                                        @error("items.{$index}.total_price") <span class="text-red-500 text-[9px] font-bold block mt-1 uppercase tracking-widest">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-1 flex justify-end md:justify-center">
                                        <button type="button" wire:click="removeItem({{ $index }})" class="p-3 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition shadow-sm" title="Hapus Baris">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            
                            @error('items')
                                <div class="text-red-500 text-[10px] font-bold mt-2 uppercase tracking-widest text-center">❌ {{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="bg-orange-50 p-4 border-t border-orange-100 flex justify-between items-center">
                            <span class="text-[10px] font-black text-orange-600 uppercase tracking-widest">Total Keseluruhan</span>
                            <div class="text-right flex items-center gap-6">
                                <div>
                                    <div class="text-[9px] font-black text-slate-400 uppercase">Total Berat</div>
                                    <div class="text-sm font-black text-slate-600">{{ number_format(collect($items)->sum(fn($item) => (float)($item['weight_kg'] ?? 0)), 2, ',', '.') }} Kg</div>
                                </div>
                                <div>
                                    <div class="text-[9px] font-black text-orange-400 uppercase">Total Pendapatan</div>
                                    <div class="text-xl font-black text-orange-600">Rp {{ number_format(collect($items)->sum(fn($item) => (float)($item['total_price'] ?? 0)), 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled" class="w-full py-5 bg-orange-600 text-white font-black rounded-2xl uppercase tracking-widest text-sm hover:bg-orange-700 transition shadow-xl shadow-orange-100 active:scale-95 disabled:bg-slate-300">
                    <span wire:loading.remove>{{ $vendorSaleId ? 'Update Data Penjualan' : 'Konfirmasi Penjualan' }}</span>
                    <span wire:loading>Memproses Data...</span>
                </button>

            </form>
        </div>
    </div>
</div>
