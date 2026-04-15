<div class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <div class="flex items-center gap-4">
            <a href="{{ route('transactions.index') }}" wire:navigate
                class="p-2 bg-white rounded-xl shadow-sm text-slate-400 hover:text-emerald-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tighter uppercase">Koreksi <span
                        class="text-emerald-600">Transaksi</span></h2>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Nasabah: {{ $employee_name }}
                </p>
            </div>
        </div>

        <form wire:submit="update"
            class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="p-8 space-y-6">
                @if (session()->has('error'))
                    <div
                        class="p-4 bg-red-50 text-red-600 rounded-xl border border-red-100 font-bold text-xs uppercase tracking-widest">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Daftar Item
                        Timbangan</label>

                    @foreach ($items as $index => $item)
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6 bg-slate-50 rounded-2xl border border-slate-100 relative group">
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Jenis
                                    Sampah</label>
                                <select wire:model="items.{{ $index }}.waste_type_id"
                                    class="w-full bg-white border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-emerald-500">
                                    @foreach ($waste_types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }} (Rp
                                            {{ number_format($type->currentPrice->price_per_kg ?? 0) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase mb-1 block">Berat
                                    (Kg)</label>
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.weight_kg"
                                    class="w-full bg-white border-slate-200 rounded-xl text-sm font-black text-slate-700 focus:ring-emerald-500">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-8 bg-slate-50/50 border-t border-slate-100 flex gap-4">
                <button type="submit"
                    class="flex-1 bg-emerald-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 active:scale-95">
                    Simpan Perubahan
                </button>
                <a href="{{ route('transactions.index') }}" wire:navigate
                    class="px-8 bg-white text-slate-400 py-4 rounded-2xl font-black text-xs uppercase tracking-widest border border-slate-200 hover:bg-slate-50 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
