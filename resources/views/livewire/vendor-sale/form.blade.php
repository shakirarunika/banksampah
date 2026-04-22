<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $vendorSaleId ? 'Edit Penjualan Vendor' : 'Tambah Penjualan Vendor' }}
        </h2>
        <a href="{{ route('vendor-sales.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Kembali
        </a>
    </div>
</x-slot>

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Tanggal Transaksi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
                            <input type="date" wire:model="transaction_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('transaction_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Vendor Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Vendor</label>
                            <input type="text" wire:model="vendor_name" placeholder="Misal: Pengepul Budi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('vendor_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Jenis Sampah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kategori Sampah</label>
                            <select wire:model="waste_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($wasteTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->code }})</option>
                                @endforeach
                            </select>
                            @error('waste_type_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Berat -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Berat (Kg)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" step="0.01" wire:model="weight_kg" class="block w-full rounded-md border-gray-300 pl-3 pr-12 focus:border-indigo-500 focus:ring-indigo-500" placeholder="0.00">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 sm:text-sm">Kg</span>
                                </div>
                            </div>
                            @error('weight_kg') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Total Harga -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Harga Penjualan</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" wire:model="total_price" class="block w-full rounded-md border-gray-300 pl-10 pr-3 focus:border-indigo-500 focus:ring-indigo-500" placeholder="0">
                            </div>
                            @error('total_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Foto Resi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Foto Resi / Bukti Timbang (Opsional)</label>
                            <input type="file" wire:model="receipt_photo" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100
                            "/>
                            <div wire:loading wire:target="receipt_photo" class="text-sm text-blue-500 mt-1">Mengunggah...</div>
                            @error('receipt_photo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            
                            @if ($receipt_photo)
                                <div class="mt-2">
                                    <span class="block text-xs text-gray-500 mb-1">Preview Baru:</span>
                                    <img src="{{ $receipt_photo->temporaryUrl() }}" class="h-32 object-cover rounded-md">
                                </div>
                            @elseif ($existing_photo)
                                <div class="mt-2">
                                    <span class="block text-xs text-gray-500 mb-1">Foto Saat Ini:</span>
                                    <img src="{{ asset('storage/' . $existing_photo) }}" class="h-32 object-cover rounded-md">
                                </div>
                            @endif
                        </div>

                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow">
                            {{ $vendorSaleId ? 'Update Data' : 'Simpan Data' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
