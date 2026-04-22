<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rekonsiliasi Bulanan') }}
        </h2>
    </div>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6 flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700">Bulan</label>
                <select wire:model.live="month" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tahun</label>
                <select wire:model.live="year" class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @for($y=date('Y'); $y>=date('Y')-5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div wire:loading class="text-sm text-gray-500 pb-2">
                Memproses data...
            </div>
        </div>

        <!-- Header Widgets (4 kotak ala Filament) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 border-blue-500">
                <h3 class="text-sm font-medium text-gray-500">Total Kg Masuk (Inbound)</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalInboundKg, 2, ',', '.') }} <span class="text-lg font-normal text-gray-500">kg</span></p>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 border-orange-500">
                <h3 class="text-sm font-medium text-gray-500">Total Kg Keluar (Outbound)</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalOutboundKg, 2, ',', '.') }} <span class="text-lg font-normal text-gray-500">kg</span></p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 {{ $shrinkageKg > 0 ? 'border-red-500' : 'border-green-500' }}">
                <h3 class="text-sm font-medium text-gray-500">Penyusutan (Shrinkage)</h3>
                <p class="mt-2 text-3xl font-bold {{ $shrinkageKg > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ number_format($shrinkageKg, 2, ',', '.') }} <span class="text-lg font-normal text-gray-500">kg</span>
                </p>
                <p class="text-sm text-gray-500 mt-1">{{ number_format($shrinkagePercent, 1, ',', '.') }}% dari Inbound</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border-t-4 {{ $profitMargin > 0 ? 'border-green-500' : 'border-red-500' }}">
                <h3 class="text-sm font-medium text-gray-500">Total Profit Margin</h3>
                <p class="mt-2 text-3xl font-bold {{ $profitMargin > 0 ? 'text-green-600' : 'text-red-600' }}">
                    Rp {{ number_format($profitMargin, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Detail Table (Perbandingan per Kategori) -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Rincian Perbandingan per Kategori Sampah</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th rowspan="2" class="px-6 py-3 border-b border-r text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100">Kategori</th>
                                <th colspan="2" class="px-6 py-3 border-b border-r text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50">Inbound (Masuk)</th>
                                <th colspan="2" class="px-6 py-3 border-b border-r text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-orange-50">Outbound (Keluar)</th>
                                <th colspan="2" class="px-6 py-3 border-b text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-green-50">Selisih</th>
                            </tr>
                            <tr>
                                <th class="px-4 py-2 border-b border-r text-right text-xs font-medium text-gray-500 uppercase bg-blue-50">Berat</th>
                                <th class="px-4 py-2 border-b border-r text-right text-xs font-medium text-gray-500 uppercase bg-blue-50">Nilai (Rp)</th>
                                <th class="px-4 py-2 border-b border-r text-right text-xs font-medium text-gray-500 uppercase bg-orange-50">Berat</th>
                                <th class="px-4 py-2 border-b border-r text-right text-xs font-medium text-gray-500 uppercase bg-orange-50">Nilai (Rp)</th>
                                <th class="px-4 py-2 border-b border-r text-right text-xs font-medium text-gray-500 uppercase bg-green-50">Penyusutan</th>
                                <th class="px-4 py-2 border-b text-right text-xs font-medium text-gray-500 uppercase bg-green-50">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($comparisonData as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap border-r font-medium text-gray-900">{{ $data['name'] }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap border-r text-right text-blue-700">{{ number_format($data['inbound_kg'], 2, ',', '.') }} kg</td>
                                    <td class="px-4 py-4 whitespace-nowrap border-r text-right text-blue-700">{{ number_format($data['inbound_price'], 0, ',', '.') }}</td>
                                    
                                    <td class="px-4 py-4 whitespace-nowrap border-r text-right text-orange-700">{{ number_format($data['outbound_kg'], 2, ',', '.') }} kg</td>
                                    <td class="px-4 py-4 whitespace-nowrap border-r text-right text-orange-700">{{ number_format($data['outbound_price'], 0, ',', '.') }}</td>
                                    
                                    <td class="px-4 py-4 whitespace-nowrap border-r text-right {{ $data['shrinkage_kg'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                        {{ number_format($data['shrinkage_kg'], 2, ',', '.') }} kg
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right {{ $data['profit'] > 0 ? 'text-green-600 font-semibold' : 'text-red-600' }}">
                                        {{ number_format($data['profit'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-bold">
                            <tr>
                                <td class="px-6 py-4 border-r text-center">TOTAL</td>
                                <td class="px-4 py-4 border-r text-right text-blue-800">{{ number_format($totalInboundKg, 2, ',', '.') }} kg</td>
                                <td class="px-4 py-4 border-r text-right text-blue-800">{{ number_format($totalInboundPrice, 0, ',', '.') }}</td>
                                <td class="px-4 py-4 border-r text-right text-orange-800">{{ number_format($totalOutboundKg, 2, ',', '.') }} kg</td>
                                <td class="px-4 py-4 border-r text-right text-orange-800">{{ number_format($totalOutboundPrice, 0, ',', '.') }}</td>
                                <td class="px-4 py-4 border-r text-right {{ $shrinkageKg > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($shrinkageKg, 2, ',', '.') }} kg</td>
                                <td class="px-4 py-4 text-right {{ $profitMargin > 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($profitMargin, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
