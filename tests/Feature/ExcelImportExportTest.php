<?php

namespace Tests\Feature;

use App\Exports\DivisionsExport;
use App\Exports\UsersExport;
use App\Exports\WasteTypeExport;
use App\Imports\DivisionsImport;
use App\Imports\UsersImport;
use App\Imports\WasteTransactionImport;
use App\Imports\WasteTypeImport;
use App\Models\Division;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WasteType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Jalur Excel lewat file .xlsx beneran (bukan cuma panggil method),
 * biar upgrade maatwebsite/excel / PhpSpreadsheet ketahuan kalau rusak.
 */
class ExcelImportExportTest extends TestCase
{
    use RefreshDatabase;

    private array $files = [];

    protected function tearDown(): void
    {
        array_map('unlink', array_filter($this->files, 'is_file'));
        parent::tearDown();
    }

    private function xlsx(array $rows): string
    {
        $book = new Spreadsheet;
        $book->getActiveSheet()->fromArray($rows);
        $path = $this->files[] = tempnam(sys_get_temp_dir(), 'xl').'.xlsx';
        (new Xlsx($book))->save($path);

        return $path;
    }

    /** Isi sheet pertama dari hasil export. */
    private function exported(object $export): array
    {
        $path = $this->files[] = tempnam(sys_get_temp_dir(), 'ex').'.xlsx';
        file_put_contents($path, Excel::raw($export, ExcelType::XLSX));

        return IOFactory::load($path)->getActiveSheet()->toArray();
    }

    public function test_import_lalu_export_master_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        Excel::import(new DivisionsImport, $this->xlsx([['nama_divisi'], ['GUDANG']]));
        Excel::import(new UsersImport, $this->xlsx([['nik', 'nama_lengkap', 'email', 'role', 'nama_divisi'], ['77001', 'Rina', null, 'karyawan', 'GUDANG']]));
        Excel::import(new WasteTypeImport, $this->xlsx([['kode_sampah', 'nama_sampah', 'satuan', 'harga_per_kg'], ['KRD', 'Kardus', 'kg', 1500]]));

        $user = User::where('employee_code', '77001')->first();
        $this->assertSame('GUDANG', $user->division->name);
        $this->assertTrue($user->must_change_password);
        $this->assertEquals(1500, WasteType::where('code', 'KRD')->first()->currentPrice->price_per_kg);

        $this->assertContains(['NIK', 'NAMA_LENGKAP', 'EMAIL', 'ROLE', 'NAMA_DIVISI'], $this->exported(new UsersExport(false)));
        $this->assertContains('77001', array_column($this->exported(new UsersExport(false)), 0));
        $this->assertContains('GUDANG', array_merge(...$this->exported(new DivisionsExport(false))));
        $this->assertContains('Kardus', array_merge(...$this->exported(new WasteTypeExport(false))));
    }

    public function test_import_transaksi_pakai_harga_dari_sistem(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['employee_code' => '77002', 'division_id' => Division::forceCreate(['name' => 'X'])->id]);
        $this->actingAs($admin);
        Excel::import(new WasteTypeImport, $this->xlsx([['kode_sampah', 'nama_sampah', 'satuan', 'harga_per_kg'], ['KRD', 'Kardus', 'kg', 2000]]));

        $import = new WasteTransactionImport;
        Excel::import($import, $this->xlsx([
            ['nik', 'jenis_sampah', 'berat', 'tanggal'],
            ['77002', 'Kardus', 2.5, '2026-01-10'],
            ['77002', 'Kardus', -1, '2026-01-10'],   // berat minus dilewati
            ['99999', 'Kardus', 1, '2026-01-10'],    // NIK tidak ada dilewati
            ['77002', 'Kard', 1, '2026-01-10'],      // nama gak persis dilewati, bukan ditebak
        ]));

        $this->assertSame(1, Transaction::count());
        $this->assertEquals(5000, $employee->balance);
        $this->assertCount(3, $import->skipped);
    }

    public function test_import_karyawan_gak_ubah_role_status_dan_akun_terhapus(): void
    {
        $this->actingAs($admin = User::factory()->create(['role' => 'admin', 'employee_code' => '1001']));
        $resign = User::factory()->create(['employee_code' => '1002', 'is_active' => false]);
        $deleted = User::factory()->create(['employee_code' => '1003']);
        $deleted->delete();
        Division::forceCreate(['name' => 'GUDANG']);

        // Tanpa kolom role & nama_divisi kosong
        Excel::import(new UsersImport, $this->xlsx([
            ['nik', 'nama_lengkap', 'email', 'nama_divisi'],
            ['1001', 'Admin', 'a@x.com', ''],
            ['1002', 'Resign', 'r@x.com', ''],
            ['1003', 'Dihapus', 'd@x.com', ''],
            ['1004', 'Baru', 'b@x.com', ''],
        ]));

        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertFalse($resign->fresh()->is_active);
        $this->assertTrue(User::onlyTrashed()->where('employee_code', '1003')->exists());
        $baru = User::where('employee_code', '1004')->first();
        $this->assertSame('karyawan', $baru->role);
        $this->assertNull($baru->division_id);
    }
}
