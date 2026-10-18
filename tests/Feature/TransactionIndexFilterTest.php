<?php

namespace Tests\Feature;

use App\Livewire\Transaction\TransactionIndex;
use App\Models\User;
use App\Models\WastePrice;
use App\Models\WasteType;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Filter bulan & sort di daftar transaksi (buat cocokin dengan logbook manual).
 */
class TransactionIndexFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private WasteType $waste;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->waste = WasteType::forceCreate(['code' => 'TST-01', 'name' => 'Plastik Test']);
        WastePrice::create([
            'waste_type_id' => $this->waste->id,
            'price_per_kg' => 2000,
            'effective_from' => now()->subYear(),
        ]);
        $this->actingAs($this->admin);
    }

    public function test_filter_bulan_pakai_tanggal_timbang_bukan_tanggal_input(): void
    {
        $budi = User::factory()->create(['role' => 'karyawan', 'name' => 'Budi']);
        $andi = User::factory()->create(['role' => 'karyawan', 'name' => 'Andi']);
        // Backdate: diinput hari ini, tapi timbangnya 2 bulan lalu
        $this->makeTransaction($budi, now()->subMonths(2)->startOfMonth()->addDays(3));
        $this->makeTransaction($andi, now());

        $component = Livewire::test(TransactionIndex::class)->set('month', now()->subMonths(2)->format('Y-m'));

        $this->assertSame(['Budi'], $component->viewData('transactions')->pluck('employee.name')->all());
    }

    public function test_sort_nama_dan_nik_bolak_balik(): void
    {
        $budi = User::factory()->create(['role' => 'karyawan', 'name' => 'Budi', 'employee_code' => '100']);
        $andi = User::factory()->create(['role' => 'karyawan', 'name' => 'Andi', 'employee_code' => '200']);
        $this->makeTransaction($budi, now());
        $this->makeTransaction($andi, now()->subDay());

        $names = fn ($c) => $c->viewData('transactions')->pluck('employee.name')->all();

        $component = Livewire::test(TransactionIndex::class);
        $this->assertSame(['Budi', 'Andi'], $names($component)); // default: tanggal timbang terbaru

        $component->call('sortBy', 'name');
        $this->assertSame(['Andi', 'Budi'], $names($component));

        $component->call('sortBy', 'name');
        $this->assertSame(['Budi', 'Andi'], $names($component));

        $component->call('sortBy', 'employee_code');
        $this->assertSame(['Budi', 'Andi'], $names($component));

        $component->call('sortBy', 'password'); // kolom di luar whitelist diabaikan
        $this->assertSame('employee_code', $component->get('sortField'));
    }

    private function makeTransaction(User $employee, $weighingAt): void
    {
        app(TransactionService::class)->createTransaction([
            'employee_id' => $employee->id,
            'officer_id' => $this->admin->id,
            'weighing_at' => $weighingAt,
        ], [['waste_type_id' => $this->waste->id, 'weight' => 1]]);
    }
}
