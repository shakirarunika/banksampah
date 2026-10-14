<?php

namespace Tests\Feature;

use App\Livewire\Master\WasteManagement;
use App\Livewire\Transaction\TransactionIndex;
use App\Models\Transaction;
use App\Models\TransactionItem;
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
 * Penjaga perbaikan celah: harga dari client, login nonaktif, register,
 * hapus jenis sampah, validasi harga/berat, dan reset transaksi.
 */
class SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $employee;

    private WasteType $waste;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->employee = User::factory()->create(['role' => 'karyawan']);
        $this->waste = WasteType::forceCreate(['code' => 'TST-01', 'name' => 'Plastik Test']);
        WastePrice::create([
            'waste_type_id' => $this->waste->id,
            'price_per_kg' => 2000,
            'effective_from' => now()->subDay(),
        ]);
    }

    public function test_harga_dan_subtotal_dari_client_diabaikan(): void
    {
        $transaction = app(TransactionService::class)->createTransaction([
            'employee_id' => $this->employee->id,
            'officer_id' => $this->admin->id,
            'weighing_at' => now(),
        ], [[
            'waste_type_id' => $this->waste->id,
            'weight' => 2,
            'price' => 999999,      // nilai tamper dari browser
            'subtotal' => 99999999, // nilai tamper dari browser
        ]]);

        $item = $transaction->items()->first();
        $this->assertEquals(2000, $item->price_at_time);
        $this->assertEquals(4000, $item->subtotal);
        $this->assertEquals(4000, $this->employee->balance);
    }

    public function test_berat_nol_atau_minus_ditolak(): void
    {
        foreach ([0, -5, 'abc'] as $weight) {
            try {
                app(TransactionService::class)->createTransaction([
                    'employee_id' => $this->employee->id,
                    'officer_id' => $this->admin->id,
                    'weighing_at' => now(),
                ], [['waste_type_id' => $this->waste->id, 'weight' => $weight]]);
                $this->fail("Berat {$weight} harusnya ditolak");
            } catch (\Exception $e) {
                $this->assertStringContainsString('Berat', $e->getMessage());
            }
        }

        $this->assertSame(0, Transaction::count());
    }

    public function test_karyawan_nonaktif_gak_bisa_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        Volt::test('pages.auth.login')
            ->set('form.employee_code', $user->employee_code)
            ->set('form.password', 'password')
            ->call('login')
            ->assertHasErrors('form.employee_code');

        $this->assertGuest();
    }

    public function test_halaman_register_sudah_ditutup(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_hapus_jenis_sampah_yang_belum_dipakai_berhasil(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(WasteManagement::class)->call('delete', $this->waste->id);

        $this->assertSoftDeleted($this->waste);
    }

    public function test_hapus_jenis_sampah_yang_sudah_dipakai_ditolak(): void
    {
        $this->actingAs($this->admin);
        $this->makeTransaction();

        Livewire::test(WasteManagement::class)->call('delete', $this->waste->id);

        $this->assertNotSoftDeleted($this->waste);
    }

    public function test_harga_minus_ditolak(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(WasteManagement::class)
            ->set('selected_waste_id', $this->waste->id)
            ->set('new_price', -100)
            ->set('effective_date', now()->format('Y-m-d\TH:i'))
            ->call('updatePrice')
            ->assertHasErrors(['new_price' => 'min']);
    }

    public function test_reset_ditolak_kalau_sudah_ada_pencairan(): void
    {
        $this->actingAs($this->admin);
        $this->makeTransaction();
        Withdrawal::create([
            'employee_id' => $this->employee->id,
            'officer_id' => $this->admin->id,
            'amount' => 1000,
            'status' => 'PENDING',
        ]);

        Livewire::test(TransactionIndex::class)->call('resetAllTransactions');

        $this->assertSame(1, Transaction::count());
    }

    public function test_reset_cuma_soft_delete_dan_item_tetap_ada(): void
    {
        $this->actingAs($this->admin);
        $transaction = $this->makeTransaction();

        Livewire::test(TransactionIndex::class)->call('resetAllTransactions');

        $this->assertSoftDeleted($transaction);
        $this->assertSame(1, TransactionItem::count());
        $this->assertEquals(0, $this->employee->balance);
    }

    private function makeTransaction(): Transaction
    {
        return app(TransactionService::class)->createTransaction([
            'employee_id' => $this->employee->id,
            'officer_id' => $this->admin->id,
            'weighing_at' => now(),
        ], [['waste_type_id' => $this->waste->id, 'weight' => 1]]);
    }
}
