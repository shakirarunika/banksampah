<?php

namespace Tests\Feature;

use App\Imports\UsersImport;
use App\Livewire\Master\UserManagement;
use App\Livewire\Transaction\BalanceLedger;
use App\Livewire\Transaction\WithdrawalCreate;
use App\Livewire\Transaction\WithdrawalRequest;
use App\Livewire\VendorSale\Reconciliation;
use App\Models\Division;
use App\Models\User;
use App\Models\WastePrice;
use App\Models\WasteType;
use App\Models\Withdrawal;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

/**
 * Fitur karyawan (pengajuan pencairan, mutasi sendiri, wajib ganti password)
 * plus perbaikan teknis (role import, rekonsiliasi).
 */
class EmployeeSelfServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->employee = User::factory()->create(['role' => 'karyawan']);

        $waste = WasteType::forceCreate(['code' => 'TST-01', 'name' => 'Plastik Test']);
        WastePrice::create(['waste_type_id' => $waste->id, 'price_per_kg' => 1000, 'effective_from' => now()->subDay()]);
        app(TransactionService::class)->createTransaction([
            'employee_id' => $this->employee->id,
            'officer_id' => $this->admin->id,
            'weighing_at' => now(),
        ], [['waste_type_id' => $waste->id, 'weight' => 300]]); // saldo 300.000
    }

    private function requestWithdrawal(int $amount)
    {
        return Livewire::actingAs($this->employee)->test(WithdrawalRequest::class)
            ->set('amount', $amount)
            ->call('submit');
    }

    public function test_pengajuan_karyawan_langsung_menahan_saldo(): void
    {
        $this->requestWithdrawal(200000)->assertHasNoErrors();

        $wd = Withdrawal::first();
        $this->assertSame('REQUESTED', $wd->status);
        $this->assertNull($wd->officer_id);
        $this->assertEquals(100000, $this->employee->balance);

        // Saldo yang lagi diajukan gak bisa diajukan lagi
        $this->requestWithdrawal(200000);
        $this->assertSame(1, Withdrawal::count());
    }

    public function test_pengajuan_di_bawah_minimum_ditolak(): void
    {
        $this->requestWithdrawal(50000)->assertHasErrors(['amount' => 'min']);
    }

    public function test_petugas_setujui_lalu_tolak_pengajuan(): void
    {
        $this->requestWithdrawal(100000);
        $this->requestWithdrawal(100000);
        [$a, $b] = Withdrawal::orderBy('id')->get();

        $page = Livewire::actingAs($this->admin)->test(WithdrawalCreate::class);
        $page->call('approveRequest', $a->id);
        $page->call('rejectRequest', $b->id);

        $this->assertSame('PENDING', $a->fresh()->status);
        $this->assertSame($this->admin->id, $a->fresh()->officer_id);
        $this->assertSame('REJECTED', $b->fresh()->status);
        $this->assertEquals(200000, $this->employee->balance); // yang ditolak balik

        // Yang sudah diproses gak bisa diproses ulang
        $page->call('rejectRequest', $a->id);
        $this->assertSame('PENDING', $a->fresh()->status);
    }

    public function test_karyawan_bisa_lihat_mutasi_sendiri(): void
    {
        $this->actingAs($this->employee)->get(route('ledger.mine'))
            ->assertOk()
            ->assertSee($this->employee->name);

        $entries = Livewire::actingAs($this->employee)->test(BalanceLedger::class)->instance()->entries();
        $this->assertEquals(300000, $entries->first()['balance']);
    }

    public function test_dashboard_karyawan_gak_nampilin_saldo_orang_lain(): void
    {
        $this->actingAs($this->employee)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Sultan Sampah');
    }

    public function test_password_default_wajib_diganti_dulu(): void
    {
        $this->employee->forceFill(['must_change_password' => true])->save();

        $this->actingAs($this->employee)->get(route('dashboard'))->assertRedirect(route('profile'));
        $this->actingAs($this->employee)->get(route('profile'))->assertOk()
            ->assertSee('Password Anda masih default')
            ->assertSee($this->employee->employee_code)   // NIK tampil sebagai username
            ->assertDontSee('Profile Information');       // form nama/email disembunyikan dulu

        Volt::test('profile.update-password-form')
            ->set('current_password', 'salah-total')
            ->set('password', 'password-baru-123')
            ->set('password_confirmation', 'password-baru-123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password'])
            ->assertSee('Password saat ini salah.');

        Volt::test('profile.update-password-form')
            ->set('current_password', 'password')
            ->set('password', $this->employee->employee_code)
            ->set('password_confirmation', $this->employee->employee_code)
            ->call('updatePassword')
            ->assertHasErrors(['password' => 'not_in']);

        Volt::test('profile.update-password-form')
            ->set('current_password', 'password')
            ->set('password', 'password-baru-123')
            ->set('password_confirmation', 'password-baru-123')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertFalse($this->employee->fresh()->must_change_password);
        $this->actingAs($this->employee->fresh())->get(route('dashboard'))->assertOk()
            ->assertSee('Password berhasil diganti');
        $this->actingAs($this->employee->fresh())->get(route('profile'))->assertSee('Profile Information');
    }

    public function test_akun_wajib_ganti_password_tetap_bisa_kirim_request_livewire(): void
    {
        $this->employee->forceFill(['must_change_password' => true])->save();
        $this->actingAs($this->employee);

        // Endpoint update Livewire (dipakai tombol Simpan) gak boleh dilempar ke profil
        $update = $this->withHeader('X-Livewire', 'true')->post(Livewire::getUpdateUri(), ['components' => []]);
        $this->assertFalse($update->isRedirect(route('profile')), 'Request Livewire diblokir middleware');

        // Header X-Livewire palsu gak bisa dipakai buat buka halaman lain
        $this->withHeader('X-Livewire', 'true')->get(route('dashboard'))->assertRedirect(route('profile'));
    }

    public function test_ganti_password_biasa_menampilkan_notifikasi_sukses(): void
    {
        $this->actingAs($this->employee);

        Volt::test('profile.update-password-form')
            ->set('current_password', 'password')
            ->set('password', 'password-baru-123')
            ->set('password_confirmation', 'password-baru-123')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertNoRedirect()
            ->assertSee('Password berhasil diganti');
    }

    public function test_karyawan_baru_dan_reset_password_wajib_ganti_password(): void
    {
        $division = Division::forceCreate(['name' => 'PRODUKSI']);

        $page = Livewire::actingAs($this->admin)->test(UserManagement::class)
            ->set('name', 'Karyawan Baru')
            ->set('employee_code', '99999')
            ->set('division_id', $division->id)
            ->call('save');

        $this->assertTrue(User::where('employee_code', '99999')->first()->must_change_password);

        $page->call('resetPassword', $this->employee->id);
        $this->assertTrue($this->employee->fresh()->must_change_password);
    }

    public function test_import_role_ngaco_jadi_karyawan(): void
    {
        $import = new UsersImport;
        $user = $import->model(['nik' => '77777', 'nama_lengkap' => 'Typo Role', 'nama_divisi' => 'X', 'role' => 'Admin ']);
        $this->assertSame('admin', $user->role);

        $user = $import->model(['nik' => '77778', 'nama_lengkap' => 'Role Aneh', 'nama_divisi' => 'X', 'role' => 'superuser']);
        $this->assertSame('karyawan', $user->role);
        $this->assertTrue($user->must_change_password);
    }

    public function test_rekonsiliasi_tahan_bulan_tidak_valid(): void
    {
        Livewire::actingAs($this->admin)->test(Reconciliation::class)
            ->set('month', '13')
            ->set('year', '')
            ->assertOk();
    }
}
