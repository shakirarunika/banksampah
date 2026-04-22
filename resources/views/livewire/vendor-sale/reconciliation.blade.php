<div class="py-6 md:py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase">
                    Rekonsiliasi <span class="text-blue-600">Bulanan</span>
                </h2>
                <p class="text-[10px] text-slate-500 font-bold tracking-widest uppercase mt-1">
                    Analisis Margin Keuntungan dan Penyusutan Berat (Inbound vs Outbound)
                </p>
            </div>
            
            <div class="flex gap-4 items-center bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center gap-2 pl-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Filter:</span>
                    <select wire:model.live="month" class="border-0 bg-transparent text-sm font-bold text-slate-700 focus:ring-0 py-2 pr-8 cursor-pointer">
                        @for($i=1; $i<=12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="w-px h-6 bg-slate-200"></div>
                <div class="flex items-center">
                    <select wire:model.live="year" class="border-0 bg-transparent text-sm font-bold text-slate-700 focus:ring-0 py-2 pr-8 cursor-pointer">
                        @for($y=date('Y'); $y>=date('Y')-5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div wire:loading class="px-3">
                    <svg class="animate-spin h-4 w-4 text-blue-500" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Header Widgets (4 kotak ala Filament) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-3xl shadow-sm p-6 border-2 border-slate-50 border-t-emerald-400">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Masuk (Inbound)</h3>
                <p class="mt-3 text-3xl font-black text-slate-800 tracking-tighter">{{ number_format($totalInboundKg, 2, ',', '.') }} <span class="text-sm font-bold text-slate-400">kg</span></p>
            </div>
            
            <div class="bg-white rounded-3xl shadow-sm p-6 border-2 border-slate-50 border-t-orange-400">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Keluar (Outbound)</h3>
                <p class="mt-3 text-3xl font-black text-slate-800 tracking-tighter">{{ number_format($totalOutboundKg, 2, ',', '.') }} <span class="text-sm font-bold text-slate-400">kg</span></p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm p-6 border-2 border-slate-50 {{ $shrinkageKg > 0 ? 'border-t-red-400' : 'border-t-emerald-400' }}">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Penyusutan (Shrinkage)</h3>
                <p class="mt-3 text-3xl font-black tracking-tighter {{ $shrinkageKg > 0 ? 'text-red-500' : 'text-emerald-500' }}">
                    {{ number_format($shrinkageKg, 2, ',', '.') }} <span class="text-sm font-bold {{ $shrinkageKg > 0 ? 'text-red-300' : 'text-emerald-300' }}">kg</span>
                </p>
                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">{{ number_format($shrinkagePercent, 1, ',', '.') }}% dari Inbound</p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm p-6 border-2 border-slate-50 {{ $profitMargin > 0 ? 'border-t-blue-500' : 'border-t-red-400' }}">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Profit Margin</h3>
                <p class="mt-3 text-3xl font-black tracking-tighter {{ $profitMargin > 0 ? 'text-blue-600' : 'text-red-500' }}">
                    <span class="text-sm font-bold {{ $profitMargin > 0 ? 'text-blue-300' : 'text-red-300' }}">Rp</span> {{ number_format($profitMargin, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Detail Table (Perbandingan per Kategori) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 border-b border-slate-50 bg-slate-50/30 flex justify-between items-center">
                <h3 class="font-black text-slate-800 text-sm tracking-tight uppercase">Rincian Perbandingan per Kategori Sampah</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th rowspan="2" class="p-4 border-b border-r border-slate-100 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50">Kategori</th>
                            <th colspan="2" class="p-4 border-b border-r border-emerald-100 text-center text-[10px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50/50">Inbound (Masuk)</th>
                            <th colspan="2" class="p-4 border-b border-r border-orange-100 text-center text-[10px] font-black text-orange-600 uppercase tracking-widest bg-orange-50/50">Outbound (Keluar)</th>
                            <th colspan="2" class="p-4 border-b border-blue-100 text-center text-[10px] font-black text-blue-600 uppercase tracking-widest bg-blue-50/50">Selisih</th>
                        </tr>
                        <tr>
                            <th class="px-4 py-3 border-b border-r border-emerald-100 text-right text-[9px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-50/50">Berat</th>
                            <th class="px-4 py-3 border-b border-r border-emerald-100 text-right text-[9px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-50/50">Nilai (Rp)</th>
                            <th class="px-4 py-3 border-b border-r border-orange-100 text-right text-[9px] font-black text-orange-500 uppercase tracking-widest bg-orange-50/50">Berat</th>
                            <th class="px-4 py-3 border-b border-r border-orange-100 text-right text-[9px] font-black text-orange-500 uppercase tracking-widest bg-orange-50/50">Nilai (Rp)</th>
                            <th class="px-4 py-3 border-b border-r border-blue-100 text-right text-[9px] font-black text-blue-500 uppercase tracking-widest bg-blue-50/50">Penyusutan</th>
                            <th class="px-4 py-3 border-b border-blue-100 text-right text-[9px] font-black text-blue-500 uppercase tracking-widest bg-blue-50/50">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($comparisonData as $data)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap border-r border-slate-50 font-black text-slate-700 text-xs uppercase">{{ $data['name'] }}</td>
                                <td class="px-4 py-4 whitespace-nowrap border-r border-slate-50 text-right font-black text-emerald-600 text-xs">{{ number_format($data['inbound_kg'], 2, ',', '.') }} <span class="text-[9px] opacity-50">kg</span></td>
                                <td class="px-4 py-4 whitespace-nowrap border-r border-slate-50 text-right font-black text-emerald-600 text-xs">{{ number_format($data['inbound_price'], 0, ',', '.') }}</td>
                                
                                <td class="px-4 py-4 whitespace-nowrap border-r border-slate-50 text-right font-black text-orange-600 text-xs">{{ number_format($data['outbound_kg'], 2, ',', '.') }} <span class="text-[9px] opacity-50">kg</span></td>
                                <td class="px-4 py-4 whitespace-nowrap border-r border-slate-50 text-right font-black text-orange-600 text-xs">{{ number_format($data['outbound_price'], 0, ',', '.') }}</td>
                                
                                <td class="px-4 py-4 whitespace-nowrap border-r border-slate-50 text-right font-black text-xs {{ $data['shrinkage_kg'] > 0 ? 'text-red-500' : 'text-slate-400' }}">
                                    {{ number_format($data['shrinkage_kg'], 2, ',', '.') }} <span class="text-[9px] opacity-50">kg</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right font-black text-xs {{ $data['profit'] > 0 ? 'text-blue-600' : 'text-red-500' }}">
                                    {{ number_format($data['profit'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-black text-xs uppercase tracking-widest border-t border-slate-200">
                        <tr>
                            <td class="px-6 py-5 border-r border-slate-200 text-center text-slate-500">TOTAL</td>
                            <td class="px-4 py-5 border-r border-slate-200 text-right text-emerald-700">{{ number_format($totalInboundKg, 2, ',', '.') }} <span class="text-[9px] opacity-50">kg</span></td>
                            <td class="px-4 py-5 border-r border-slate-200 text-right text-emerald-700">{{ number_format($totalInboundPrice, 0, ',', '.') }}</td>
                            <td class="px-4 py-5 border-r border-slate-200 text-right text-orange-700">{{ number_format($totalOutboundKg, 2, ',', '.') }} <span class="text-[9px] opacity-50">kg</span></td>
                            <td class="px-4 py-5 border-r border-slate-200 text-right text-orange-700">{{ number_format($totalOutboundPrice, 0, ',', '.') }}</td>
                            <td class="px-4 py-5 border-r border-slate-200 text-right {{ $shrinkageKg > 0 ? 'text-red-600' : 'text-slate-700' }}">{{ number_format($shrinkageKg, 2, ',', '.') }} <span class="text-[9px] opacity-50">kg</span></td>
                            <td class="px-4 py-5 text-right {{ $profitMargin > 0 ? 'text-blue-700' : 'text-red-600' }}">{{ number_format($profitMargin, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
