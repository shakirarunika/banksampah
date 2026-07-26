<?php

namespace Tests\Feature;

use App\Livewire\Master\UserManagement;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTimbangTest extends TestCase
{
    use RefreshDatabase;

    private User $adminTimbang;

    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminTimbang = User::factory()->create(['role' => 'admin_timbang']);
        $this->division = Division::forceCreate(['name' => 'Produksi']);
    }

    public function test_admin_timbang_punya_akses_petugas_dan_halaman_karyawan(): void
    {
        $this->assertTrue($this->adminTimbang->can('access-petugas'));
        $this->assertTrue($this->adminTimbang->can('manage-users'));
        $this->assertFalse($this->adminTimbang->can('access-admin'));

        $this->actingAs($this->adminTimbang)->get('/master/karyawan')->assertOk();
        $this->actingAs($this->adminTimbang)->get('/transaksi')->assertOk();
    }

    public function test_petugas_biasa_tetap_gak_bisa_buka_halaman_karyawan(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $this->actingAs($petugas)->get('/master/karyawan')->assertForbidden();
    }

    public function test_admin_timbang_bisa_nambah_karyawan_tapi_role_dipaksa_karyawan(): void
    {
        Livewire::actingAs($this->adminTimbang)
            ->test(UserManagement::class)
            ->set('name', 'Budi Test')
            ->set('employee_code', '99.001')
            ->set('division_id', $this->division->id)
            ->set('role', 'admin') // coba escalate — harus digagalkan
            ->call('save');

        $this->assertDatabaseHas('users', [
            'employee_code' => '99.001',
            'role' => 'karyawan',
        ]);
    }

    public function test_admin_timbang_gak_bisa_hapus_edit_atau_reset(): void
    {
        $target = User::factory()->create();

        foreach (['delete', 'edit', 'resetPassword'] as $action) {
            Livewire::actingAs($this->adminTimbang)
                ->test(UserManagement::class)
                ->call($action, $target->id)
                ->assertStatus(403);
        }

        $this->assertNull($target->fresh()->deleted_at);
    }
}
