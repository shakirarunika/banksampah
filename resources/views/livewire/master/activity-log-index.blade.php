<div class="py-6 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- Header --}}
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">
                Log <span class="text-violet-600">Aktivitas</span>
            </h2>
            <p class="text-[10px] text-slate-500 font-bold tracking-widest uppercase mt-1">
                Rekam jejak setiap aksi penting yang terjadi di sistem
            </p>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex flex-col md:flex-row gap-4 items-center justify-between">

                {{-- Search --}}
                <div class="relative w-full md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <input type="text" wire:model.live="search" placeholder="Cari nama pelaku atau deskripsi..."
                        class="w-full pl-10 pr-4 py-3 bg-white border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all">
                </div>

                {{-- Filter by action --}}
                <select wire:model.live="filterAction"
                    class="w-full md:w-56 px-4 py-3 bg-white border-slate-200 rounded-xl text-[10px] font-black text-slate-600 uppercase tracking-widest focus:ring-2 focus:ring-violet-500 transition-all">
                    <option value="">Semua Aksi</option>
                    @foreach($actionTypes as $type)
                        <option value="{{ $type }}">{{ str_replace('_', ' ', strtoupper($type)) }}</option>
                    @endforeach
                </select>

                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">
                    Total: {{ $logs->total() }} Log
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 font-black uppercase text-[10px] tracking-[0.2em] border-b border-slate-100">
                            <th class="p-6">Waktu</th>
                            <th class="p-6">Pelaku</th>
                            <th class="p-6 text-center">Aksi</th>
                            <th class="p-6">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($logs as $log)
                            <tr class="hover:bg-violet-50/20 transition-all">
                                <td class="p-6">
                                    <div class="text-[11px] font-black text-slate-800">
                                        {{ $log->created_at->format('d M Y') }}
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-400 font-mono mt-0.5">
                                        {{ $log->created_at->format('H:i:s') }}
                                    </div>
                                </td>

                                <td class="p-6">
                                    <div class="font-black text-slate-800 uppercase text-xs">
                                        {{ $log->actor?->name ?? '— (Akun Dihapus)' }}
                                    </div>
                                    <div class="text-[10px] font-bold text-violet-500 uppercase mt-0.5">
                                        {{ $log->actor?->role ?? '' }}
                                    </div>
                                </td>

                                <td class="p-6 text-center">
                                    @php
                                        $badgeConfig = match($log->action) {
                                            'void_transaction'      => ['bg-red-100 text-red-700 border-red-200',      '⊘ Void Transaksi'],
                                            'reset_all_transactions'=> ['bg-red-200 text-red-800 border-red-300',      '💣 Reset Semua'],
                                            'reset_password'        => ['bg-amber-100 text-amber-700 border-amber-200','🔑 Reset Password'],
                                            'delete_user'           => ['bg-red-100 text-red-700 border-red-200',      '🗑 Hapus User'],
                                            'create_user'           => ['bg-emerald-100 text-emerald-700 border-emerald-200','✚ Tambah User'],
                                            'update_user'           => ['bg-blue-100 text-blue-700 border-blue-200',   '✎ Edit User'],
                                            'delete_waste'          => ['bg-red-100 text-red-700 border-red-200',      '🗑 Hapus Sampah'],
                                            'update_price'          => ['bg-orange-100 text-orange-700 border-orange-200','↑ Ubah Harga'],
                                            'new_withdrawal'        => ['bg-blue-100 text-blue-700 border-blue-200',   '↓ Pencairan'],
                                            'complete_withdrawal'   => ['bg-emerald-100 text-emerald-700 border-emerald-200','✔ Cair Selesai'],
                                            default                 => ['bg-slate-100 text-slate-600 border-slate-200', strtoupper($log->action)],
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1.5 rounded-full text-[8px] font-black uppercase tracking-widest border {{ $badgeConfig[0] }}">
                                        {{ $badgeConfig[1] }}
                                    </span>
                                </td>

                                <td class="p-6">
                                    <p class="text-[11px] text-slate-600 font-bold leading-relaxed max-w-md">
                                        {{ $log->description }}
                                    </p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Belum ada log aktivitas</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-slate-50 bg-slate-50/30">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
