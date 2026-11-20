<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WastePrice;
use App\Models\WasteType;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WastePriceByDateTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    private User $employee;

    private User $officer;

    private WasteType $lama;

    private WasteType $baru;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TransactionService::class);
        $this->employee = User::factory()->create(['role' => 'karyawan']);
        $this->officer = User::factory()->create(['role' => 'petugas']);

        // Jenis lama: harga Maret 1.000, naik 1.200 per 1 Sep, diganti list baru per 8 Okt
        $this->lama = WasteType::create(['code' => 'T001', 'name' => 'KARDUS', 'unit' => 'Kg', 'inactive_from' => '2026-10-08 00:00:00']);
        WastePrice::create(['waste_type_id' => $this->lama->id, 'price_per_kg' => 1000, 'effective_from' => '2026-03-05 14:30:00']);
        WastePrice::create(['waste_type_id' => $this->lama->id, 'price_per_kg' => 1200, 'effective_from' => '2026-09-01T10:00']);

        $this->baru = WasteType::create(['code' => 'T002', 'name' => 'Kardus', 'unit' => 'Kg']);
        WastePrice::create(['waste_type_id' => $this->baru->id, 'price_per_kg' => 1700, 'effective_from' => '2026-10-08 00:00:00']);
    }

    private function timbang(WasteType $waste, string $date)
    {
        return $this->service->createTransaction(
            ['employee_id' => $this->employee->id, 'officer_id' => $this->officer->id, 'weighing_at' => $date],
            [['waste_type_id' => $waste->id, 'weight' => 2]]
        )->items->first();
    }

    public function test_backdate_pakai_harga_di_tanggal_timbang(): void
    {
        $this->assertEquals(1000, $this->timbang($this->lama, '2026-08-15 09:00:00')->price_at_time);
        $this->assertEquals(2400, $this->timbang($this->lama, '2026-09-20 09:00:00')->subtotal);
        $this->assertEquals(1700, $this->timbang($this->baru, '2026-10-08 09:00:00')->price_at_time);
    }

    public function test_harga_yang_mulai_siang_hari_berlaku_seharian(): void
    {
        $this->assertEquals(1200, $this->timbang($this->lama, '2026-09-01 07:00:00')->price_at_time);
    }

    public function test_jenis_lama_ditolak_setelah_diganti_dan_jenis_baru_ditolak_sebelum_berlaku(): void
    {
        $this->assertEquals(['T001'], WasteType::usableOn('2026-08-15')->where('code', 'like', 'T%')->pluck('code')->all());
        $this->assertEquals(['T002'], WasteType::usableOn('2026-10-08')->where('code', 'like', 'T%')->pluck('code')->all());

        $this->expectExceptionMessage('tidak berlaku pada tanggal 08-10-2026');
        $this->timbang($this->lama, '2026-10-08 09:00:00');
    }

    public function test_jenis_baru_gak_bisa_dipakai_buat_tanggal_sebelum_berlaku(): void
    {
        $this->expectExceptionMessage('tidak berlaku pada tanggal 15-08-2026');
        $this->timbang($this->baru, '2026-08-15 09:00:00');
    }

    public function test_layar_input_ganti_tanggal_ikut_ganti_pilihan_dan_harga_keranjang(): void
    {
        \Livewire\Livewire::actingAs($this->officer)
            ->test(\App\Livewire\Transaction\TransactionCreate::class)
            ->set('form.transaction_date', '2026-09-20')
            ->assertViewHas('wasteTypes', fn ($list) => $list->firstWhere('code', 'T001')?->price_on_date == 1200
                && ! $list->contains('code', 'T002'))
            ->set('form.selected_waste', $this->lama->id)
            ->set('form.weight', 2)
            ->call('addItem')
            ->assertSet('form.items.0.price', 1200.0)
            ->set('form.transaction_date', '2026-08-15')
            ->assertSet('form.items.0.subtotal', 2000.0)
            ->set('form.transaction_date', '2026-10-10')
            ->assertSet('form.items', []);
    }

    public function test_setoran_kembar_diperingatkan_dulu_baru_tersimpan_setelah_konfirmasi(): void
    {
        $this->timbang($this->baru, '2026-10-10 09:00:00'); // sudah ada Kardus 2 kg di 10 Okt

        $page = \Livewire\Livewire::actingAs($this->officer)
            ->test(\App\Livewire\Transaction\TransactionCreate::class)
            ->set('employee', $this->employee)
            ->set('form.transaction_date', '2026-10-10')
            ->set('form.selected_waste', $this->baru->id)->set('form.weight', 2)->call('addItem')
            ->call('saveTransaction');

        $this->assertCount(1, $page->get('duplicateWarning'));
        $this->assertSame(1, \App\Models\Transaction::count());

        $page->call('saveTransaction', true)->assertRedirect(route('transactions.index'));
        $this->assertSame(2, \App\Models\Transaction::count());
    }

    public function test_harga_masa_depan_belum_jadi_harga_sekarang(): void
    {
        WastePrice::create(['waste_type_id' => $this->baru->id, 'price_per_kg' => 9999, 'effective_from' => now()->addMonth()]);

        $this->assertEquals(1700, $this->baru->fresh()->currentPrice->price_per_kg);
    }
}
