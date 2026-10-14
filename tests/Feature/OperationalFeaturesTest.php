<?php

namespace Tests\Feature;

use App\Livewire\Report;
use App\Livewire\Transaction\BalanceLedger;
use App\Livewire\Transaction\WithdrawalCreate;
use App\Models\Division;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WastePrice;
use App\Models\WasteType;
use App\Models\Withdrawal;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Struk timbangan, mutasi saldo, riwayat pencairan, dan laporan periode.
 */
class OperationalFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $employee;

    private WasteType $waste;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->employee = User::factory()->create(['role' => 'karyawan', 'name' => 'Budi Penyetor']);
        $this->waste = WasteType::forceCreate(['code' => 'TST-01', 'name' => 'Plastik Test']);
        WastePrice::create([
            'waste_type_id' => $this->waste->id,
            'price_per_kg' => 1000,
            'effective_from' => now()->subYear(),
        ]);
    }

    private function deposit(User $user, float $kg, $at = null): Transaction
    {
        return app(TransactionService::class)->createTransaction([
            'employee_id' => $user->id,
            'officer_id' => $this->admin->id,
            'weighing_at' => $at ?? now(),
        ], [['waste_type_id' => $this->waste->id, 'weight' => $kg]]);
    }

    private function withdraw(User $user, int $amount, string $status = 'PENDING'): Withdrawal
    {
        return Withdrawal::create([
            'employee_id' => $user->id,
            'officer_id' => $this->admin->id,
            'amount' => $amount,
            'status' => $status,
        ]);
    }

    public function test_struk_timbangan_bisa_dicetak_petugas_tapi_bukan_karyawan(): void
    {
        $trx = $this->deposit($this->employee, 3);

        $this->actingAs($this->admin)->get(route('transactions.print', $trx))
            ->assertOk()
            ->assertSee('BST-'.str_pad($trx->id, 6, '0', STR_PAD_LEFT))
            ->assertSee('3.000');

        $this->actingAs($this->employee)->get(route('transactions.print', $trx))->assertForbidden();
    }

    public function test_saldo_akhir_mutasi_sama_dengan_saldo_sistem(): void
    {
        $this->deposit($this->employee, 200, now()->subDays(3)); // +200.000
        $this->withdraw($this->employee, 150000, 'COMPLETED');
        $void = $this->deposit($this->employee, 50, now()->subDay());
        app(TransactionService::class)->voidTransaction($void); // gak dihitung
        $this->deposit($this->employee, 10); // +10.000

        $entries = Livewire::actingAs($this->admin)
            ->test(BalanceLedger::class, ['user' => $this->employee])
            ->instance()
            ->entries();

        $this->assertCount(3, $entries);
        $this->assertEquals($this->employee->balance, $entries->first()['balance']);
        $this->assertEquals(60000, $entries->first()['balance']);
    }

    public function test_karyawan_gak_bisa_buka_mutasi(): void
    {
        $this->actingAs($this->employee)->get(route('ledger.show', $this->employee))->assertForbidden();
    }

    public function test_riwayat_pencairan_bisa_dicari_dan_difilter(): void
    {
        $other = User::factory()->create(['name' => 'Siti Lain']);
        $this->withdraw($this->employee, 100000, 'PENDING');
        $this->withdraw($other, 200000, 'COMPLETED');

        Livewire::actingAs($this->admin)->test(WithdrawalCreate::class)
            ->set('history_search', 'Budi')
            ->assertViewHas('recent_withdrawals', fn ($p) => $p->total() === 1)
            ->set('history_search', '')
            ->set('history_status', 'COMPLETED')
            ->assertViewHas('recent_withdrawals', fn ($p) => $p->total() === 1 && $p->first()->employee_id === $other->id);
    }

    public function test_laporan_hanya_hitung_periode_dan_divisi_yang_dipilih(): void
    {
        $divA = Division::forceCreate(['name' => 'PRODUKSI']);
        $divB = Division::forceCreate(['name' => 'GUDANG']);
        $this->employee->forceFill(['division_id' => $divA->id])->save();
        $other = User::factory()->create(['division_id' => $divB->id]);

        $this->deposit($this->employee, 5, now()->startOfMonth()->addHour()); // masuk
        $this->deposit($this->employee, 99, now()->subMonths(2));             // di luar periode
        $this->withdraw($this->employee, 2000);
        $this->deposit($other, 7);

        $component = Livewire::actingAs($this->admin)->test(Report::class);
        $rows = $component->instance()->rows()->keyBy('id');

        $this->assertEquals(5, $rows[$this->employee->id]->setoran_kg);
        $this->assertEquals(5000, $rows[$this->employee->id]->setoran_rp);
        $this->assertEquals(2000, $rows[$this->employee->id]->pencairan_rp);
        $this->assertEquals(7, $rows[$other->id]->setoran_kg);
        $this->assertFalse($rows->has($this->admin->id)); // gak ada aktivitas

        $component->set('division_id', $divB->id);
        $this->assertEquals([$other->id], $component->instance()->rows()->pluck('id')->all());

        $component->call('exportExcel')->assertFileDownloaded();
    }

    public function test_laporan_khusus_admin(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $this->actingAs($petugas)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('reports.index'))->assertOk();
    }
}
