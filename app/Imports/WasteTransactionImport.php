<?php

namespace App\Imports;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\WasteType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date; // WAJIB ADA BUAT KONVERSI EXCEL

class WasteTransactionImport implements ToCollection, WithHeadingRow
{
    /** Baris yang gagal diimpor, buat ditampilkan ke user setelah selesai. */
    public array $skipped = [];

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2: index mulai 0 dan baris 1 = header

                // 1. Cari Karyawan berdasarkan NIK (Header di Excel harus: nik)
                $user = User::where('employee_code', $row['nik'])->first();
                if (! $user) {
                    $this->skipped[] = "Baris {$rowNumber}: NIK '{$row['nik']}' tidak ditemukan";

                    continue;
                }

                // 2. Cari Jenis Sampah (Header di Excel harus: jenis_sampah)
                // Exact match dulu biar "Plastik" gak nyangkut ke "Plastik Keras"
                $waste = WasteType::with('currentPrice')
                    ->where('name', $row['jenis_sampah'])
                    ->first()
                    ?? WasteType::with('currentPrice')
                        ->where('name', 'like', '%'.$row['jenis_sampah'].'%')
                        ->first();
                if (! $waste) {
                    $this->skipped[] = "Baris {$rowNumber}: jenis sampah '{$row['jenis_sampah']}' tidak ditemukan";

                    continue;
                }

                $price = $waste->currentPrice->price_per_kg ?? 0;
                $weight = (float) $row['berat']; // Header di Excel: berat

                // 3. LOGIC PENYELAMAT TANGGAL (Header di Excel: tanggal)
                $tanggalRaw = $row['tanggal'];
                $tanggalParsed = null;

                if (is_numeric($tanggalRaw)) {
                    // Jika Excel ngasih format "Date" (Serial Number 45xxx)
                    $tanggalParsed = Carbon::instance(Date::excelToDateTimeObject($tanggalRaw));
                } else {
                    // Jika Excel ngasih format Teks (Contoh: 10-01-2025)
                    $tanggalParsed = Carbon::parse($tanggalRaw);
                }

                // Set jam biar gak 00:00 semua (pake jam sekarang aja biar rapi)
                $weighingAt = $tanggalParsed->setHour(now()->hour)->setMinute(now()->minute);

                // 4. Buat Transaksi Induk
                $transaction = Transaction::create([
                    'employee_id' => $user->id,
                    'officer_id' => auth()->id(),
                    'weighing_at' => $weighingAt,
                    'status' => \App\Enums\TransactionStatus::POSTED->value,
                ]);

                // 5. Buat Item Transaksi
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'waste_type_id' => $waste->id,
                    'weight_kg' => $weight,
                    'price_at_time' => $price,
                    'subtotal' => $weight * $price,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
