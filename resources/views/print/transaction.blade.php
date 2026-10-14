<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Timbangan #{{ $transaction->id }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            @page {
                size: A5 portrait;
                margin: 1cm;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background-color: white;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-slate-100 text-black antialiased font-sans flex justify-center py-8 print:py-0 print:bg-white">

    <div
        class="bg-white w-full max-w-lg p-6 md:p-8 shadow-xl border border-slate-200 print:border-none print:shadow-none print:max-w-none print:p-0 relative">

        <div class="border-b-2 border-black pb-3 mb-6 flex justify-between items-end">
            <div>
                <h1 class="text-xl font-black uppercase tracking-widest">BANK SAMPAH DASI AYA</h1>
                <p class="text-[10px] font-bold mt-0.5 uppercase tracking-widest text-slate-600">Struk Timbangan</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black uppercase tracking-widest">Ref:
                    BST-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p class="text-[9px] font-bold uppercase text-slate-600">Tgl:
                    {{ $transaction->weighing_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        @if ($transaction->status !== \App\Enums\TransactionStatus::POSTED)
            <div class="mb-4 p-2 border-2 border-red-600 text-red-600 text-center text-xs font-black uppercase tracking-widest">
                Transaksi {{ $transaction->status->label() }}
            </div>
        @endif

        <table class="w-full text-xs font-bold mb-6">
            <tr>
                <td class="py-1.5 w-28 uppercase text-slate-500 tracking-widest">Nama</td>
                <td class="py-1.5 w-4">:</td>
                <td class="py-1.5 uppercase text-sm font-black">{{ $transaction->employee->name }}</td>
            </tr>
            <tr>
                <td class="py-1.5 uppercase text-slate-500 tracking-widest">NIK / Dept</td>
                <td class="py-1.5">:</td>
                <td class="py-1.5 uppercase">{{ $transaction->employee->employee_code }} /
                    {{ $transaction->employee->division->name ?? '-' }}</td>
            </tr>
        </table>

        <table class="w-full text-xs mb-6">
            <thead>
                <tr class="border-b border-black uppercase text-[10px] tracking-widest">
                    <th class="py-1.5 text-left">Jenis</th>
                    <th class="py-1.5 text-right">Berat</th>
                    <th class="py-1.5 text-right">Harga/kg</th>
                    <th class="py-1.5 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->items as $item)
                    <tr class="border-b border-dashed border-slate-300 font-bold">
                        <td class="py-1.5 uppercase">{{ $item->wasteType?->name ?? 'Sampah' }}</td>
                        <td class="py-1.5 text-right">{{ number_format($item->weight_kg, 2, ',', '.') }} kg</td>
                        <td class="py-1.5 text-right">{{ number_format($item->price_at_time, 0, ',', '.') }}</td>
                        <td class="py-1.5 text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-black">
                    <td class="pt-3 uppercase tracking-widest">Total</td>
                    <td class="pt-3 text-right">{{ number_format($transaction->items->sum('weight_kg'), 2, ',', '.') }} kg</td>
                    <td></td>
                    <td class="pt-3 text-right text-base">Rp {{ number_format($transaction->items->sum('subtotal'), 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="grid grid-cols-2 gap-2 text-center mt-8 text-[9px] font-black uppercase tracking-widest">
            <div class="flex flex-col items-center">
                <p class="mb-16">Penyetor,</p>
                <p class="border-b border-black w-full pb-1 truncate px-1">{{ $transaction->employee->name }}</p>
            </div>
            <div class="flex flex-col items-center">
                <p class="mb-16">Petugas,</p>
                <p class="border-b border-black w-full pb-1 truncate px-1">{{ $transaction->officer->name }}</p>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-200 text-center no-print">
            <button onclick="window.print()"
                class="px-6 py-2 bg-emerald-600 text-white text-xs font-black rounded uppercase tracking-widest shadow hover:bg-emerald-700 transition">
                🖨️ Cetak
            </button>
            <button onclick="window.close()"
                class="px-6 py-2 bg-slate-200 text-slate-600 text-xs font-black rounded uppercase tracking-widest shadow hover:bg-slate-300 transition ml-2">
                Tutup Layar
            </button>
        </div>

    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>

</html>
