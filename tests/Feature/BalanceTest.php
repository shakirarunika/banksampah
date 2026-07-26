<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Enums\WithdrawalStatus;
use App\Livewire\Transaction\WithdrawalCreate;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\WasteType;
use App\Models\Withdrawal;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Satu-satunya penjaga jalur duit: saldo, pencairan, dan void.
 */
class BalanceTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    private User $officer;

    private WasteType $waste;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = User::factory()->create(['role' => 'karyawan']);
        $this->officer = User::factory()->create(['role' => 'petugas']);
        $this->waste = WasteType::forceCreate([
            'code' => 'TST-01',
            'name' => 'Plastik Test',
        ]);
    }

    /** Bikin transaksi POSTED senilai $subtotal buat si employee. */
    private function earn(int $subtotal, string $status = 'POSTED'): Transaction
    {
        $transaction = Transaction::create([
            'employee_id' => $this->employee->id,
            'officer_id' => $this->officer->id,
            'weighing_at' => now(),
            'status' => $status,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'waste_type_id' => $this->waste->id,
            'weight_kg' => 1,
            'price_at_time' => $subtotal,
            'subtotal' => $subtotal,
        ]);

        return $transaction;
    }

    public function test_balance_hanya_hitung_posted_dikurangi_pencairan_aktif(): void
    {
        $this->earn(200000);
        $this->earn(50000, TransactionStatus::CANCELLED->value); // gak boleh kehitung

        Withdrawal::create([
            'employee_id' => $this->employee->id,
            'officer_id' => $this->officer->id,
            'amount' => 120000,
            'status' => WithdrawalStatus::PENDING->value,
        ]);

        $this->assertEquals(80000, $this->employee->balance);
    }

    public function test_pencairan_melebihi_saldo_ditolak(): void
    {
        $this->earn(150000);

        Livewire::actingAs($this->officer)
            ->test(WithdrawalCreate::class)
            ->set('search_nik', $this->employee->employee_code)
            ->set('amount', 200000)
            ->call('saveWithdrawal');

        $this->assertSame(0, Withdrawal::count());
    }

    public function test_pencairan_sesuai_saldo_berhasil(): void
    {
        $this->earn(150000);

        Livewire::actingAs($this->officer)
            ->test(WithdrawalCreate::class)
            ->set('search_nik', $this->employee->employee_code)
            ->set('amount', 150000)
            ->call('saveWithdrawal');

        $this->assertDatabaseHas('withdrawals', [
            'employee_id' => $this->employee->id,
            'amount' => 150000,
            'status' => WithdrawalStatus::PENDING->value,
        ]);
    }

    public function test_void_yang_bikin_saldo_minus_gagal(): void
    {
        $besar = $this->earn(150000);
        $this->earn(100000);

        Withdrawal::create([
            'employee_id' => $this->employee->id,
            'officer_id' => $this->officer->id,
            'amount' => 150000,
            'status' => WithdrawalStatus::COMPLETED->value,
        ]);

        // Saldo 100rb, void transaksi 150rb harus meledak
        $this->expectExceptionMessage('GAGAL VOID');
        app(TransactionService::class)->voidTransaction($besar->fresh());
    }

    public function test_void_normal_berhasil(): void
    {
        $transaction = $this->earn(150000);

        app(TransactionService::class)->voidTransaction($transaction->fresh());

        $this->assertEquals(TransactionStatus::CANCELLED, $transaction->fresh()->status);
        $this->assertEquals(0, $this->employee->balance);
    }
}
