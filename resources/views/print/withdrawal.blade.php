<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pencairan Kasbon #{{ $withdrawal->id }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* MENTOR HACK: Ubah ke Portrait & Ukuran Kertas Diperkecil */
        @media print {
            @page {
                size: A5 portrait;
                /* Berdiri, ukuran setengah A4 */
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
                <p class="text-[10px] font-bold mt-0.5 uppercase tracking-widest text-slate-600">Form Pencairan Tabungan
                </p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black uppercase tracking-widest">Ref:
                    BSW-{{ str_pad($withdrawal->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p class="text-[9px] font-bold uppercase text-slate-600">Tgl:
                    {{ $withdrawal->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="mb-8">
            <table class="w-full text-xs font-bold">
                <tr>
                    <td class="py-1.5 w-28 uppercase text-slate-500 tracking-widest">Nama</td>
                    <td class="py-1.5 w-4">:</td>
                    <td class="py-1.5 uppercase text-sm font-black">{{ $withdrawal->employee->name }}</td>
                </tr>
                <tr>
                    <td class="py-1.5 uppercase text-slate-500 tracking-widest">NIK / Dept</td>
                    <td class="py-1.5">:</td>
                    <td class="py-1.5 uppercase">{{ $withdrawal->employee->employee_code }} /
                        {{ $withdrawal->employee->division->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-1.5 uppercase text-slate-500 tracking-widest">Keterangan</td>
                    <td class="py-1.5">:</td>
                    <td class="py-1.5 uppercase">{{ $withdrawal->notes ?? 'Pencairan Rutin' }}</td>
                </tr>
                <tr>
                    <td
                        class="py-3 uppercase text-slate-500 tracking-widest border-t border-dashed border-slate-300 mt-2 block">
                        Total Cair</td>
                    <td class="py-3 border-t border-dashed border-slate-300 mt-2">:</td>
                    <td class="py-3 border-t border-dashed border-slate-300 mt-2">
                        <span class="text-xl font-black">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="grid grid-cols-3 gap-2 text-center mt-8 text-[9px] font-black uppercase tracking-widest">
            <div class="flex flex-col items-center">
                <p class="mb-16">Pemohon,</p>
                <p class="border-b border-black w-full pb-1 truncate px-1">{{ $withdrawal->employee->name }}</p>
            </div>
            <div class="flex flex-col items-center">
                <p class="mb-16">HRGA,</p>
                <p class="border-b border-black w-full pb-1 truncate px-1">{{ $withdrawal->officer->name }}</p>
            </div>
            <div class="flex flex-col items-center">
                <p class="mb-16">Koperasi,</p>
                <p class="border-b border-black w-full pb-1"></p>
                <p class="text-[7px] mt-0.5 text-slate-500">(Stempel/TTD)</p>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-200 text-center no-print">
            <button onclick="window.print()"
                class="px-6 py-2 bg-emerald-600 text-white text-xs font-black rounded uppercase tracking-widest shadow hover:bg-blue-700 transition">
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
