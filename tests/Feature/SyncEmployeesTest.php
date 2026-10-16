<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SyncEmployeesTest extends TestCase
{
    use RefreshDatabase;

    private string $file;

    protected function setUp(): void
    {
        parent::setUp();

        $old = Division::forceCreate(['name' => 'HRGA Business Unit']);
        Division::forceCreate(['name' => 'HRGA Operation']);

        User::factory()->create(['employee_code' => '04.1', 'name' => 'Budi Santoso', 'division_id' => $old->id, 'role' => 'admin']);
        User::factory()->create(['employee_code' => '04.2', 'name' => 'Nasruloh Hakim', 'division_id' => $old->id]);
        User::factory()->create(['employee_code' => '04.3', 'name' => 'Siti Aminah', 'division_id' => $old->id]);
        User::factory()->create(['employee_code' => '04.4', 'name' => 'Tetap Sama', 'division_id' => $old->id]);

        $book = new Spreadsheet;
        $book->getActiveSheet()->setTitle('RESIGN')->fromArray([['NIK', 'Nama Pegawai', 'Departemen', 'Tgl. Berhenti'], ['04.4', 'Tetap Sama', 'HRGA Business Unit', 46023]]);
        $book->createSheet()->setTitle('AKTIF (tarikan 14 Okt 2026)')->fromArray([
            ['NIK', 'Nama Pegawai', 'Departemen'],
            [null, null, null],
            ['04.1', 'BUDI  SANTOSO', 'HRGA Operation '],   // pindah (beda kapital/spasi tetap dianggap sama)
            ['04.2', 'Nasrulloh Hakim', 'HRGA Business Unit'], // typo nama -> dikoreksi
            ['04.3', 'Joko Widodo', 'Legal'],                 // nama beda jauh -> dilewati
            ['04.4', 'Tetap Sama', 'HRGA Business Unit'],     // tidak berubah
            ['04.9', 'Karyawan Baru', 'Legal'],               // daftar baru + divisi baru
            ['04.9', 'Karyawan Baru', 'Legal'],               // dobel -> dilewati
        ]);
        $this->file = tempnam(sys_get_temp_dir(), 'sync').'.xlsx';
        (new Xlsx($book))->save($this->file);
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    public function test_preview_tidak_menyimpan_apa_pun(): void
    {
        $this->artisan('karyawan:sync', ['file' => $this->file])
            ->expectsOutputToContain('Karyawan baru: 1')
            ->assertSuccessful();

        $this->assertNull(User::where('employee_code', '04.9')->first());
        $this->assertSame('HRGA Business Unit', User::where('employee_code', '04.1')->first()->division->name);
    }

    public function test_apply_sesuai_aturan(): void
    {
        $this->artisan('karyawan:sync', ['file' => $this->file, '--apply' => true])->assertSuccessful();

        $user = fn ($nik) => User::where('employee_code', $nik)->first();

        // Pindah departemen, role & nama asli gak disentuh
        $this->assertSame('HRGA Operation', $user('04.1')->division->name);
        $this->assertSame('admin', $user('04.1')->role);
        $this->assertSame('Budi Santoso', $user('04.1')->name);

        // Typo nama ikut Excel
        $this->assertSame('Nasrulloh Hakim', $user('04.2')->name);

        // Nama beda jauh: dilewati total
        $this->assertSame('Siti Aminah', $user('04.3')->name);
        $this->assertSame('HRGA Business Unit', $user('04.3')->division->name);

        // Karyawan baru: sekali saja, password NIK, wajib ganti, divisi baru dibuat
        $this->assertSame(1, User::where('employee_code', '04.9')->count());
        $new = $user('04.9');
        $this->assertSame('karyawan', $new->role);
        $this->assertTrue($new->is_active);
        $this->assertTrue($new->must_change_password);
        $this->assertTrue(Hash::check('04.9', $new->password));
        $this->assertSame('Legal', $new->division->name);

        // Sheet RESIGN diabaikan
        $this->assertTrue($user('04.4')->is_active);
    }

    public function test_file_tanpa_sheet_aktif_ditolak(): void
    {
        $book = new Spreadsheet;
        $book->getActiveSheet()->setTitle('Sheet1')->fromArray([['NIK', 'Nama Pegawai', 'Departemen']]);
        (new Xlsx($book))->save($this->file);

        $this->artisan('karyawan:sync', ['file' => $this->file, '--apply' => true])->assertFailed();
    }
}
