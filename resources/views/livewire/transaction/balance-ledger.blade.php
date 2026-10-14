<div class="py-8 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8 flex flex-col md:flex-row justify-between md:items-end gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tighter uppercase">
                    Mutasi <span class="text-emerald-600">Saldo</span>
                </h2>
                <p class="text-xs text-slate-500 font-bold tracking-widest uppercase mt-1">
                    {{ $user->name }} · NIK {{ $user->employee_code }} · {{ $user->division->name ?? 'NON-DIVISI' }}
                </p>
            </div>
            <div class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-sm text-right">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Saldo Saat Ini</div>
                <div class="text-2xl font-black text-emerald-600 tracking-tighter">
                    Rp {{ number_format($entries->first()['balance'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-400 font-black uppercase text-[10px] tracking-widest">
                        <tr>
                            <th class="p-4 md:p-6">Tanggal</th>
                            <th class="p-4 md:p-6">Keterangan</th>
                            <th class="p-4 md:p-6 text-right">Masuk</th>
                            <th class="p-4 md:p-6 text-right">Keluar</th>
                            <th class="p-4 md:p-6 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($entries as $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-4 md:p-6 text-slate-500 font-mono text-[10px] whitespace-nowrap">
                                    {{ $row['date']->format('d/m/Y') }}<br>{{ $row['date']->format('H:i') }}
                                </td>
                                <td class="p-4 md:p-6">
                                    <div class="font-black text-xs uppercase {{ $row['amount'] >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                        {{ $row['type'] }} <span class="font-mono text-slate-400">{{ $row['ref'] }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-bold mt-0.5">{{ $row['description'] }}</div>
                                </td>
                                <td class="p-4 md:p-6 text-right font-black text-emerald-600 text-xs whitespace-nowrap">
                                    {{ $row['amount'] >= 0 ? 'Rp '.number_format($row['amount'], 0, ',', '.') : '' }}
                                </td>
                                <td class="p-4 md:p-6 text-right font-black text-red-600 text-xs whitespace-nowrap">
                                    {{ $row['amount'] < 0 ? 'Rp '.number_format(-$row['amount'], 0, ',', '.') : '' }}
                                </td>
                                <td class="p-4 md:p-6 text-right font-black text-slate-800 text-xs whitespace-nowrap">
                                    Rp {{ number_format($row['balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-16 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    Belum ada mutasi saldo
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
