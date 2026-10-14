<div class="py-8 md:py-12 bg-slate-50 min-h-screen print:bg-white print:py-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <div class="flex flex-col md:flex-row justify-between md:items-end gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tighter uppercase">
                    Laporan <span class="text-emerald-600">Bank Sampah</span>
                </h2>
                <p class="text-xs text-slate-500 font-bold tracking-widest uppercase mt-1">
                    Periode {{ $from->format('d/m/Y') }} – {{ $to->format('d/m/Y') }}
                    @if ($division_id)
                        · {{ $divisions->firstWhere('id', $division_id)?->name }}
                    @endif
                </p>
            </div>
            <div class="flex gap-2 print:hidden">
                <button wire:click="exportExcel"
                    class="px-5 py-3 bg-emerald-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-emerald-700 transition">
                    Export Excel
                </button>
                <button onclick="window.print()"
                    class="px-5 py-3 bg-slate-800 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-900 transition">
                    Cetak / PDF
                </button>
            </div>
        </div>

        <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-4 print:hidden">
            <label class="block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dari Tanggal</span>
                <input type="date" wire:model.live="date_from"
                    class="mt-1 w-full border-2 border-slate-200 rounded-xl px-4 py-2 text-sm font-bold focus:border-emerald-500 focus:ring-0">
            </label>
            <label class="block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sampai Tanggal</span>
                <input type="date" wire:model.live="date_to"
                    class="mt-1 w-full border-2 border-slate-200 rounded-xl px-4 py-2 text-sm font-bold focus:border-emerald-500 focus:ring-0">
            </label>
            <label class="block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Divisi</span>
                <select wire:model.live="division_id"
                    class="mt-1 w-full border-2 border-slate-200 rounded-xl px-4 py-2 text-sm font-bold focus:border-emerald-500 focus:ring-0">
                    <option value="">Semua Divisi</option>
                    @foreach ($divisions as $div)
                        <option value="{{ $div->id }}">{{ $div->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ([
                ['Karyawan Aktif', number_format($rows->count(), 0, ',', '.'), 'text-slate-800'],
                ['Total Setoran', number_format($rows->sum('setoran_kg'), 2, ',', '.').' kg', 'text-slate-800'],
                ['Nilai Setoran', 'Rp '.number_format($rows->sum('setoran_rp'), 0, ',', '.'), 'text-emerald-600'],
                ['Total Pencairan', 'Rp '.number_format($rows->sum('pencairan_rp'), 0, ',', '.'), 'text-red-600'],
            ] as [$label, $value, $color])
                <div class="bg-white p-4 md:p-6 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $label }}</div>
                    <div class="text-lg md:text-2xl font-black tracking-tighter mt-1 {{ $color }}">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <h3 class="p-4 md:p-6 font-black text-slate-800 text-sm uppercase tracking-tight border-b border-slate-50">Rekap Per Divisi</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-black uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="p-4">Divisi</th>
                            <th class="p-4 text-right">Karyawan</th>
                            <th class="p-4 text-right">Setoran (kg)</th>
                            <th class="p-4 text-right">Setoran (Rp)</th>
                            <th class="p-4 text-right">Pencairan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 font-bold text-slate-700">
                        @forelse ($byDivision as $name => $d)
                            <tr>
                                <td class="p-4 uppercase">{{ $name }}</td>
                                <td class="p-4 text-right">{{ $d['karyawan'] }}</td>
                                <td class="p-4 text-right">{{ number_format($d['setoran_kg'], 2, ',', '.') }}</td>
                                <td class="p-4 text-right text-emerald-600">{{ number_format($d['setoran_rp'], 0, ',', '.') }}</td>
                                <td class="p-4 text-right text-red-600">{{ number_format($d['pencairan_rp'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Tidak ada data di periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <h3 class="p-4 md:p-6 font-black text-slate-800 text-sm uppercase tracking-tight border-b border-slate-50">Rincian Per Karyawan</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-black uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="p-4">NIK</th>
                            <th class="p-4">Nama</th>
                            <th class="p-4">Divisi</th>
                            <th class="p-4 text-right">Setoran (kg)</th>
                            <th class="p-4 text-right">Setoran (Rp)</th>
                            <th class="p-4 text-right">Pencairan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 font-bold text-slate-700">
                        @forelse ($rows as $u)
                            <tr wire:key="report-{{ $u->id }}">
                                <td class="p-4 font-mono">{{ $u->employee_code }}</td>
                                <td class="p-4 uppercase">
                                    <a href="{{ route('ledger.show', $u->id) }}" wire:navigate
                                        class="hover:text-emerald-600 hover:underline">{{ $u->name }}</a>
                                </td>
                                <td class="p-4 uppercase">{{ $u->division->name ?? '-' }}</td>
                                <td class="p-4 text-right">{{ number_format($u->setoran_kg, 2, ',', '.') }}</td>
                                <td class="p-4 text-right text-emerald-600">{{ number_format($u->setoran_rp, 0, ',', '.') }}</td>
                                <td class="p-4 text-right text-red-600">{{ number_format($u->pencairan_rp, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Tidak ada data di periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
