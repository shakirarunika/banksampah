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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    
                    <div class="space-y-6">
                        <!-- Tanggal Transaksi -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">1. Tanggal Transaksi</label>
                            <input type="date" wire:model="transaction_date" class="w-full border-2 border-slate-200 rounded-2xl p-4 text-lg font-black text-slate-800 focus:border-orange-500 focus:ring-0 transition bg-slate-50">
                            @error('transaction_date') <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌ {{ $message }}</span> @enderror
                        </div>

                        <!-- Vendor Name -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">2. Nama Vendor / Pengepul</label>
                            <input type="text" wire:model="vendor_name" placeholder="Misal: Pengepul Budi" class="w-full border-2 border-slate-200 rounded-2xl p-4 text-lg font-black text-slate-800 focus:border-orange-500 focus:ring-0 transition bg-slate-50 placeholder:text-slate-300">
                            @error('vendor_name') <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌ {{ $message }}</span> @enderror
                        </div>

                        <!-- Jenis Sampah -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">3. Kategori Sampah</label>
                            <select wire:model="waste_type_id" class="w-full border-2 border-slate-200 rounded-2xl p-4 text-lg font-black text-slate-800 focus:border-orange-500 focus:ring-0 transition bg-slate-50">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($wasteTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->code }})</option>
                                @endforeach
                            </select>
                            @error('waste_type_id') <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌ {{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Berat -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">4. Berat Timbangan</label>
                            <div class="relative">
                                <input type="number" step="0.01" wire:model="weight_kg" class="w-full pr-14 border-2 border-slate-200 rounded-2xl p-5 text-4xl font-black text-right text-slate-800 focus:border-orange-500 focus:ring-0 transition shadow-inner placeholder:text-slate-200" placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                                    <span class="text-slate-400 font-black text-xl">Kg</span>
                                </div>
                            </div>
                            @error('weight_kg') <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌ {{ $message }}</span> @enderror
                        </div>

                        <!-- Total Harga -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">5. Total Pendapatan</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <span class="text-slate-400 font-black text-xl">Rp</span>
                                </div>
                                <input type="number" wire:model="total_price" class="w-full pl-14 border-2 border-slate-200 rounded-2xl p-5 text-4xl font-black text-right text-orange-600 focus:border-orange-500 focus:ring-0 transition shadow-inner placeholder:text-slate-200" placeholder="0">
                            </div>
                            @error('total_price') <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌ {{ $message }}</span> @enderror
                        </div>

                        <!-- Foto Resi -->
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block font-mono">6. Foto Resi / Bukti Timbang</label>
                            <input type="file" wire:model="receipt_photo" class="w-full border-2 border-dashed border-slate-200 rounded-2xl p-4 text-sm font-bold text-slate-500 focus:border-orange-500 focus:ring-0 transition bg-slate-50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200" />
                            <div wire:loading wire:target="receipt_photo" class="text-[10px] font-black text-orange-500 mt-2 uppercase tracking-widest animate-pulse">Mengunggah Foto...</div>
                            @error('receipt_photo') <span class="text-red-500 text-[10px] font-bold block mt-2 uppercase tracking-widest">❌ {{ $message }}</span> @enderror
                            
                            @if ($receipt_photo)
                                <div class="mt-4 p-2 border-2 border-dashed border-orange-200 rounded-2xl text-center">
                                    <span class="block text-[9px] font-black text-orange-400 mb-2 uppercase tracking-widest">Preview Baru:</span>
                                    <img src="{{ $receipt_photo->temporaryUrl() }}" class="h-32 object-contain mx-auto rounded-xl">
                                </div>
                            @elseif ($existing_photo)
                                <div class="mt-4 p-2 border-2 border-dashed border-slate-200 rounded-2xl text-center">
                                    <span class="block text-[9px] font-black text-slate-400 mb-2 uppercase tracking-widest">Foto Saat Ini:</span>
                                    <img src="{{ asset('storage/' . $existing_photo) }}" class="h-32 object-contain mx-auto rounded-xl">
                                </div>
                            @endif
                        </div>

                        <button type="submit" wire:loading.attr="disabled" class="w-full mt-4 py-5 bg-orange-600 text-white font-black rounded-2xl uppercase tracking-widest text-sm hover:bg-orange-700 transition shadow-xl shadow-orange-100 active:scale-95 disabled:bg-slate-300">
                            <span wire:loading.remove>{{ $vendorSaleId ? 'Update Data Penjualan' : 'Konfirmasi Penjualan' }}</span>
                            <span wire:loading>Memproses Data...</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
