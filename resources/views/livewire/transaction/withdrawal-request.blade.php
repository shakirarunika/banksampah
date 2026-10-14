<div class="py-8 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div>
            <h2 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tighter uppercase">
                Ajukan <span class="text-emerald-600">Pencairan</span>
            </h2>
            <p class="text-xs text-slate-500 font-bold tracking-widest uppercase mt-1">
                Saldo diproses petugas HRGA & ditransfer via koperasi
            </p>
        </div>

        @if (session()->has('success'))
            <div class="p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 font-bold text-xs uppercase tracking-widest rounded-r-xl">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 font-bold text-xs uppercase tracking-widest rounded-r-xl">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm space-y-6">
            <div class="flex justify-between items-end border-b border-slate-100 pb-4">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Saldo Tersedia</span>
                <span class="text-3xl font-black text-emerald-600 tracking-tighter">
                    Rp {{ number_format($balance, 0, ',', '.') }}
                </span>
            </div>

            <form wire:submit="submit" class="space-y-4">
                <label class="block">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nominal (min. Rp 100.000)</span>
                    <input type="number" wire:model="amount" min="100000" step="1000" inputmode="numeric"
                        class="mt-1 w-full border-2 border-slate-200 rounded-2xl p-4 text-2xl font-black text-right text-slate-800 focus:border-emerald-500 focus:ring-0">
                    @error('amount')
                        <span class="text-red-600 text-[10px] font-bold block mt-2 uppercase tracking-widest">{{ $message }}</span>
                    @enderror
                </label>
                <label class="block">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Catatan (opsional)</span>
                    <input type="text" wire:model="notes" maxlength="255"
                        class="mt-1 w-full border-2 border-slate-200 rounded-xl p-3 text-sm font-bold text-slate-700 focus:border-emerald-500 focus:ring-0">
                </label>
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-4 bg-emerald-600 text-white font-black rounded-2xl uppercase tracking-widest text-sm hover:bg-emerald-700 transition disabled:bg-slate-300">
                    <span wire:loading.remove wire:target="submit">Kirim Pengajuan</span>
                    <span wire:loading wire:target="submit">Mengirim...</span>
                </button>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="font-black text-slate-800 text-sm tracking-tight uppercase">Pengajuan Saya</h3>
                <a href="{{ route('ledger.mine') }}" wire:navigate
                    class="text-[10px] font-black text-emerald-600 uppercase tracking-widest hover:underline">Riwayat Lengkap →</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse ($requests as $wd)
                    <div class="p-4 md:px-6 flex justify-between items-center gap-4">
                        <div>
                            <div class="font-black text-slate-800 text-sm">Rp {{ number_format($wd->amount, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-slate-400 font-bold font-mono">{{ $wd->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest
                            {{ match ($wd->status) {
                                'COMPLETED' => 'bg-emerald-100 text-emerald-700',
                                'REQUESTED' => 'bg-blue-100 text-blue-700',
                                'REJECTED' => 'bg-slate-100 text-slate-500',
                                default => 'bg-amber-100 text-amber-700',
                            } }}">
                            {{ match ($wd->status) {
                                'REQUESTED' => 'Menunggu Persetujuan',
                                'PENDING' => 'Disetujui, Menunggu Transfer',
                                'COMPLETED' => 'Selesai',
                                'REJECTED' => 'Ditolak',
                                default => $wd->status,
                            } }}
                        </span>
                    </div>
                @empty
                    <div class="p-10 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Belum ada pengajuan</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
