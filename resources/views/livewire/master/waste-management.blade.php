<div class="py-6 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        @if (session()->has('message'))
            <div
                class="p-4 bg-emerald-100 text-emerald-700 rounded-2xl shadow-sm border-l-4 border-emerald-500 font-bold text-sm">
                <span class="uppercase tracking-widest text-[10px]">{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 bg-red-100 text-red-700 rounded-2xl shadow-sm border-l-4 border-red-500 font-bold text-sm">
                <span class="uppercase tracking-widest text-[10px]">{{ session('error') }}</span>
            </div>
        @endif

        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">
                Master Data <span class="text-emerald-600">Jenis Sampah</span>
            </h2>
            <p class="text-[10px] text-slate-500 font-bold tracking-widest uppercase mt-1">
                Atur kategori dan harga sampah
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 sticky top-6">
                <h3 class="font-bold mb-6 text-gray-700 flex items-center uppercase tracking-tighter">
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-md mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                    </span>
                    {{ $selected_waste_id ? 'Detail / Update Harga' : 'Tambah Jenis Sampah' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kode
                            Sampah</label>
                        <input type="text" wire:model="code" placeholder="Contoh: KDS"
                            class="w-full border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 font-mono uppercase {{ $selected_waste_id ? 'bg-gray-50' : '' }}"
                            {{ $selected_waste_id ? 'readonly' : '' }}>
                        @error('code')
                            <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama
                            Sampah</label>
                        <input type="text" wire:model="name" placeholder="Contoh: Kardus Bekas"
                            class="w-full border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 font-bold {{ $selected_waste_id ? 'bg-gray-50' : '' }}"
                            {{ $selected_waste_id ? 'readonly' : '' }}>
                        @error('name')
                            <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    @if (!$selected_waste_id)
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Harga Awal
                                (Rp/Kg)</label>
                            <input type="number" wire:model="price" placeholder="0"
                                class="w-full border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500 font-mono text-emerald-600 font-bold">
                            @error('price')
                                <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="pt-4">
                        @if ($selected_waste_id)
                            <button wire:click="$set('selected_waste_id', null)"
                                class="w-full bg-gray-100 text-gray-600 py-2.5 rounded-md font-bold hover:bg-gray-200 transition mb-2 uppercase text-xs">
                                Kembali ke Tambah Baru
                            </button>
                        @else
                            <button wire:click="saveWaste"
                                class="w-full bg-emerald-600 text-white py-2.5 rounded-md font-bold hover:bg-blue-700 transition shadow-md shadow-emerald-100 uppercase text-xs">
                                Simpan Jenis Sampah
                            </button>
                        @endif
                    </div>
                </div>

                @if ($selected_waste_id)
                    <div class="mt-8 pt-6 border-t border-dashed">
                        <h4 class="text-[10px] font-black text-emerald-600 uppercase mb-4 tracking-widest text-center">
                            Update Harga Baru</h4>
                        <div class="space-y-3 p-4 bg-emerald-50 rounded-lg">
                            <input type="number" wire:model="new_price"
                                class="w-full border-gray-200 rounded-md text-sm font-bold" placeholder="Harga Baru">
                            <input type="datetime-local" wire:model="effective_date"
                                class="w-full border-gray-200 rounded-md text-sm">
                            <button wire:click="updatePrice"
                                class="w-full bg-emerald-600 text-white py-2 rounded-md font-bold text-xs uppercase shadow-sm">
                                Update Sekarang
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-2 space-y-8">

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
                                Master Data Management
                            </h4>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase mt-1">Kelola data sampah masal
                                via spreadsheet</p>

                            <div class="flex gap-4 mt-3 justify-center lg:justify-start">
                                <a href="#" wire:click.prevent="downloadTemplate"
                                    class="text-[9px] font-black text-emerald-700 underline hover:text-emerald-900 uppercase tracking-[0.2em]">
                                    📥 Template
                                </a>
                                <a href="#" wire:click.prevent="exportExcel"
                                    class="text-[9px] font-black text-blue-600 underline hover:text-blue-800 uppercase tracking-[0.2em]">
                                    📤 Export Data
                                </a>
                            </div>
                        </div>

                        <form wire:submit="importExcel"
                            class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto"
                            x-data="{ fileName: 'Pilih File Master...' }"
                            @file-imported.window="fileName = 'Pilih File Master...'; document.getElementById('file_excel').value = ''">
                            <div class="relative w-full sm:w-60">
                                <input type="file" wire:model="file_excel" id="file_excel" class="hidden"
                                    @change="fileName = $event.target.files[0] ? $event.target.files[0].name : 'Pilih File Master...'" />
                                <label for="file_excel"
                                    class="flex items-center justify-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-white transition-all shadow-sm">
                                    <span class="text-[10px] font-black text-slate-500 uppercase truncate" x-text="fileName">
                                        Pilih File Master...
                                    </span>
                                </label>
                            </div>

                            <button type="submit" wire:loading.attr="disabled" wire:target="file_excel,importExcel"
                                class="w-full sm:w-auto bg-emerald-800 text-white px-8 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-emerald-200 disabled:opacity-50 transition-all active:scale-95">
                                <span wire:loading.remove wire:target="file_excel,importExcel">Update Masal</span>
                                <span wire:loading wire:target="file_excel" class="flex items-center gap-2">
                                    <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Mengupload...
                                </span>
                                <span wire:loading wire:target="importExcel" class="flex items-center gap-2">
                                    <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
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
                        <div class="relative w-full md:w-80">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2"
                                        stroke-linecap="round" />
                                </svg>
                            </div>
                            <input type="text" wire:model.live="search" placeholder="Cari Kode atau Nama..."
                                class="w-full pl-10 pr-4 py-3 bg-white border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm">
                        </div>

                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Total: {{ $wasteTypes->total() }} Jenis Sampah
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-50/80 text-slate-500 font-black uppercase text-[10px] tracking-[0.2em] border-b border-slate-100">
                                    <th class="p-6">Kode</th>
                                    <th class="p-6">Nama Sampah</th>
                                    <th class="p-6">Harga / Unit</th>
                                    <th class="p-6 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($wasteTypes as $type)
                                    <tr class="hover:bg-emerald-50/20 transition-all"
                                        wire:key="waste-{{ $type->id }}">
                                        <td class="p-6 font-mono font-black text-slate-500 text-xs">
                                            {{ $type->code }}</td>
                                        <td class="p-6 font-black text-slate-800 uppercase text-xs">
                                            {{ $type->name }}</td>
                                        <td class="p-6">
                                            <div class="flex items-center gap-2">
                                                <span class="font-black text-emerald-600 text-sm">
                                                    Rp
                                                    {{ number_format($type->currentPrice->price_per_kg ?? 0, 0, ',', '.') }}
                                                </span>
                                                <button wire:click="viewHistory({{ $type->id }})"
                                                    class="p-1.5 text-slate-300 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div
                                                class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter italic">
                                                per {{ $type->unit }}</div>
                                        </td>
                                        <td class="p-6">
                                            <div class="flex justify-center items-center gap-2">
                                                <button wire:click="selectWaste({{ $type->id }})"
                                                    class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl font-black text-[10px] uppercase hover:bg-emerald-600 hover:text-white transition shadow-sm border border-emerald-100">
                                                    Edit Harga
                                                </button>
                                                <button
                                                    onclick="confirm('Yakin lo? Seluruh riwayat harga juga bakal lenyap!') || event.stopImmediatePropagation()"
                                                    wire:click="delete({{ $type->id }})"
                                                    class="p-2 text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="p-20 text-center text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                                            Data Kosong</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 bg-slate-50/30 border-t border-slate-100">
                        {{ $wasteTypes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($showHistoryModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all"
        wire:click.self="$set('showHistoryModal', false)">
        <div
            class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 uppercase tracking-tighter">Histori Harga</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                            {{ $name }}</p>
                    </div>
                </div>
                <button wire:click="$set('showHistoryModal', false)"
                    class="text-slate-300 hover:text-slate-600 transition text-2xl">&times;</button>
            </div>

            <div class="p-0 max-h-[60vh] overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-slate-50 sticky top-0 text-slate-400 font-black uppercase tracking-widest text-[10px] border-b">
                        <tr>
                            <th class="p-4">Tanggal Efektif</th>
                            <th class="p-4">Harga / {{ $unit }}</th>
                            <th class="p-4">Admin Pelaksana</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach ($priceHistory as $h)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 text-slate-500 font-mono">
                                    {{ \Carbon\Carbon::parse($h->effective_from)->format('d/m/Y H:i') }}</td>
                                <td class="p-4">
                                    <div class="text-emerald-600 font-black text-sm">Rp
                                        {{ number_format($h->price_per_kg, 0, ',', '.') }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-black text-slate-500 uppercase">
                                            {{ substr($h->admin->name ?? 'S', 0, 1) }}
                                        </div>
                                        <div class="uppercase text-[10px] font-black text-slate-700 tracking-tight">
                                            {{ $h->admin->name ?? 'SYSTEM' }}</div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if (count($priceHistory) == 0)
                    <div
                        class="p-10 text-center text-slate-400 font-bold uppercase text-[10px] tracking-widest italic">
                        Belum ada riwayat perubahan harga.</div>
                @endif
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 text-right">
                <button wire:click="$set('showHistoryModal', false)"
                    class="px-6 py-2 bg-white border border-slate-200 text-slate-500 rounded-lg font-black text-[10px] uppercase hover:bg-slate-100 transition shadow-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endif
</div>
