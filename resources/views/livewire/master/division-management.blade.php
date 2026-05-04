<div class="py-6 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        @if (session()->has('message'))
            <div
                class="p-4 bg-emerald-100 text-emerald-700 rounded-2xl shadow-sm border-l-4 border-emerald-500 font-bold text-sm">
                <span class="uppercase tracking-widest text-[10px]">{{ session('message') }}</span>
            </div>
        @endif

        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">
                Master Data <span class="text-emerald-600">Divisi</span>
            </h2>
            <p class="text-[10px] text-slate-500 font-bold tracking-widest uppercase mt-1">
                Kelola struktur departemen Bank Sampah Dasi Aya
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 sticky top-6">
                    <h3
                        class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-8 flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 21V5a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        {{ $isEditMode ? 'Update Divisi' : 'Tambah Divisi' }}
                    </h3>

                    <div class="space-y-6">
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Nama
                                Departemen</label>
                            <input type="text" wire:model="name" placeholder="CONTOH: PRODUKSI"
                                class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-black text-slate-700 uppercase transition-all shadow-sm">
                            @error('name')
                                <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pt-4 space-y-3">
                            <button wire:click="save"
                                class="w-full bg-emerald-600 text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 active:scale-95">
                                {{ $isEditMode ? 'Simpan Perubahan' : 'Daftarkan Divisi' }}
                            </button>

                            @if ($isEditMode)
                                <button wire:click="cancelEdit"
                                    class="w-full bg-slate-100 text-slate-500 py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition">
                                    Batal
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
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
                                📊 Divisi Data Management
                            </h4>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase mt-1">Kelola departemen pabrik
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
                            x-data="{ fileName: 'Pilih File Divisi...' }"
                            @file-imported.window="fileName = 'Pilih File Divisi...'; document.getElementById('file_excel').value = ''">
                            <div class="relative w-full sm:w-60">
                                <input type="file" wire:model="file_excel" id="file_excel" class="hidden"
                                    @change="fileName = $event.target.files[0] ? $event.target.files[0].name : 'Pilih File Divisi...'" />
                                <label for="file_excel"
                                    class="flex items-center justify-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-white transition-all shadow-sm">
                                    <span class="text-[10px] font-black text-slate-500 uppercase truncate" x-text="fileName">
                                        Pilih File Divisi...
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
                            <input type="text" wire:model.live="search" placeholder="Cari nama divisi..."
                                class="w-full pl-10 pr-4 py-3 bg-white border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm">
                        </div>

                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Total: {{ $divisions->total() }} Departemen
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-50/80 text-slate-500 font-black uppercase text-[10px] tracking-[0.2em] border-b border-slate-100">
                                    <th class="p-6">Nama Divisi / Departemen</th>
                                    <th class="p-6 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse ($divisions as $div)
                                    <tr class="hover:bg-emerald-50/20 transition-all"
                                        wire:key="div-{{ $div->id }}">
                                        <td class="p-6">
                                            <div class="font-black text-slate-800 uppercase text-xs tracking-tight">
                                                {{ $div->name }}</div>
                                            <div
                                                class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                                                Official Department</div>
                                        </td>
                                        <td class="p-6">
                                            <div class="flex justify-center items-center gap-2">
                                                <button wire:click="edit({{ $div->id }})"
                                                    class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl font-black text-[10px] uppercase hover:bg-emerald-600 hover:text-white transition shadow-sm border border-emerald-100">
                                                    Edit
                                                </button>
                                                <button
                                                    onclick="confirm('Hapus divisi {{ $div->name }}? Hati-hati Bos!') || event.stopImmediatePropagation()"
                                                    wire:click="delete({{ $div->id }})"
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
                                        <td colspan="2" class="p-20 text-center">
                                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">
                                                Divisi tidak ditemukan</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 bg-slate-50/30 border-t border-slate-100">
                        {{ $divisions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
