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

        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">
                Master Data <span class="text-emerald-600">Karyawan</span>
            </h2>
            <p class="text-[10px] text-slate-500 font-bold tracking-widest uppercase mt-1">
                Kelola akses dan informasi nasabah Bank Sampah Dasi Aya
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
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        {{ $isEditMode ? 'Update Karyawan' : 'Registrasi Baru' }}
                    </h3>

                    <div class="space-y-6">
                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">NIK /
                                ID Karyawan</label>
                            <input type="text" wire:model="employee_code" placeholder="CONTOH: 04.9073"
                                class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-mono font-black text-slate-700 uppercase transition-all shadow-sm">
                            @error('employee_code')
                                <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Nama
                                Lengkap</label>
                            <input type="text" wire:model="name" placeholder="NAMA SESUAI ID"
                                class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-black text-slate-700 uppercase transition-all shadow-sm">
                            @error('name')
                                <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Departemen</label>
                            <select wire:model="division_id"
                                class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-black text-slate-700 uppercase text-xs transition-all shadow-sm">
                                <option value="">-- PILIH DIVISI --</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <span class="text-red-500 text-[10px] font-bold mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Akses
                                Sistem (Role)</label>
                            <select wire:model="role"
                                class="w-full px-4 py-3 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-black text-slate-700 uppercase text-xs transition-all shadow-sm">
                                <option value="karyawan">Nasabah (User)</option>
                                <option value="petugas">Petugas Timbangan</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>

                        @if ($isEditMode)
                            <div
                                class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <input type="checkbox" wire:model="is_active" id="is_active"
                                    class="rounded-lg border-slate-300 text-emerald-600 focus:ring-emerald-500 h-5 w-5">
                                <label for="is_active"
                                    class="text-[10px] font-black text-slate-600 uppercase cursor-pointer">Status Akun
                                    Aktif</label>
                            </div>
                        @endif

                        <div class="pt-4 space-y-3">
                            <button wire:click="save"
                                class="w-full bg-emerald-600 text-white py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 active:scale-95">
                                {{ $isEditMode ? 'Simpan Perubahan' : 'Daftarkan Karyawan' }}
                            </button>

                            @if ($isEditMode)
                                <button wire:click="cancelEdit"
                                    class="w-full bg-slate-100 text-slate-500 py-4 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition">
                                    Batal / Registrasi Baru
                                </button>
                            @else
                                <p class="text-[9px] text-slate-400 font-bold uppercase text-center">*Password otomatis
                                    diset sesuai NIK</p>
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
                                👥 Employee Data Hub
                            </h4>
                            <p class="text-[10px] font-bold text-emerald-500 uppercase mt-1">Registrasi karyawan masal
                                via spreadsheet</p>

                            <div class="flex gap-4 mt-3 justify-center lg:justify-start">
                                <a href="#" wire:click.prevent="downloadTemplate"
                                    class="text-[9px] font-black text-emerald-700 underline hover:text-emerald-900 uppercase tracking-[0.2em]">
                                    📥 Template Excel
                                </a>
                                <a href="#" wire:click.prevent="exportExcel"
                                    class="text-[9px] font-black text-blue-600 underline hover:text-blue-800 uppercase tracking-[0.2em]">
                                    📤 Export Data
                                </a>
                            </div>
                        </div>

                        <form wire:submit="importExcel"
                            class="flex flex-col sm:flex-row items-center gap-4 w-full lg:w-auto"
                            x-data="{ fileName: 'Pilih File Karyawan...' }"
                            @file-imported.window="fileName = 'Pilih File Karyawan...'; document.getElementById('file_excel').value = ''">
                            <div class="relative w-full sm:w-60">
                                <input type="file" wire:model="file_excel" id="file_excel" class="hidden"
                                    @change="fileName = $event.target.files[0] ? $event.target.files[0].name : 'Pilih File Karyawan...'" />
                                <label for="file_excel"
                                    class="flex items-center justify-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-white transition-all shadow-sm">
                                    <span class="text-[10px] font-black text-slate-500 uppercase truncate" x-text="fileName">
                                        Pilih File Karyawan...
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
                            <input type="text" wire:model.live="search" placeholder="Cari NIK atau Nama..."
                                class="w-full pl-10 pr-4 py-3 bg-white border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm">
                        </div>

                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            Terdaftar: {{ $users->total() }} Karyawan
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-50/80 text-slate-500 font-black uppercase text-[10px] tracking-[0.2em] border-b border-slate-100">
                                    <th class="p-6">Informasi Karyawan</th>
                                    <th class="p-6">Departemen</th>
                                    <th class="p-6 text-center">Status</th>
                                    <th class="p-6 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($users as $u)
                                    <tr class="hover:bg-emerald-50/20 transition-all"
                                        wire:key="user-{{ $u->id }}">
                                        <td class="p-6">
                                            <div class="font-black text-slate-800 uppercase text-xs tracking-tight">
                                                {{ $u->name }}</div>
                                            <div class="text-[10px] font-bold text-emerald-600 font-mono mt-1">NIK:
                                                {{ $u->employee_code }}</div>
                                        </td>

                                        <td class="p-6 text-xs">
                                            <span
                                                class="px-2 py-1 bg-slate-100 rounded text-[9px] font-black text-slate-500 uppercase tracking-tighter">
                                                {{ $u->division->name ?? 'NON-DIVISI' }}
                                            </span>
                                        </td>

                                        <td class="p-6 text-center">
                                            <span
                                                class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border
                                                {{ $u->is_active ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-red-100 text-red-700 border-red-200' }}">
                                                {{ $u->is_active ? 'Aktif' : 'Off' }}
                                            </span>
                                        </td>

                                        <td class="p-6">
                                            <div class="flex justify-center items-center gap-2">
                                                <button wire:click="edit({{ $u->id }})"
                                                    class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl font-black text-[10px] uppercase hover:bg-emerald-600 hover:text-white transition shadow-sm border border-emerald-100">
                                                    Profil
                                                </button>

                                                {{-- Tombol Reset Password --}}
                                                <button
                                                    wire:click="resetPassword({{ $u->id }})"
                                                    wire:confirm="Reset password {{ $u->name }} ke NIK-nya ({{ $u->employee_code }})?  Karyawan harus diberitahu secara manual."
                                                    class="p-2 text-slate-300 hover:text-amber-500 hover:bg-amber-50 rounded-xl transition-colors"
                                                    title="Reset Password ke NIK">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>

                                                {{-- Tombol Hapus --}}
                                                <button
                                                    wire:click="delete({{ $u->id }})"
                                                    wire:confirm="Hapus akses karyawan ini? Tindakan ini permanen!"
                                                    class="p-2 text-slate-300 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors"
                                                    title="Hapus Karyawan">
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 bg-slate-50/30 border-t border-slate-100">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
