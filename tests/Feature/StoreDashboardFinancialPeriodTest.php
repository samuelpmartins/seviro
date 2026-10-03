<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreDashboardFinancialPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_cards_use_the_inclusive_selected_date_range(): void
    {
        $this->travelTo(Carbon::parse('2026-07-20 12:00:00'));
        [$owner, $store] = $this->createStoreOwner();

        $this->createOrder($store, 'PERIOD-START', '2026-07-18 00:00:00', 'paid', 12);
        $this->createOrder($store, 'PERIOD-END', '2026-07-19 23:59:59', 'paid', 18);
        $this->createOrder($store, 'PERIOD-PENDING', '2026-07-19 15:00:00', 'pending', 90);
        $this->createOrder($store, 'PERIOD-OUTSIDE', '2026-07-20 00:00:00', 'paid', 7);

        $this->actingAs($owner)
            ->get(route('store.dashboard', [
                'financial_start_date' => '2026-07-18',
                'financial_end_date' => '2026-07-19',
            ]))
            ->assertOk()
            ->assertViewHas('periodSales', 30)
            ->assertViewHas('periodOrders', 2)
            ->assertViewHas('totalSales', 37)
            ->assertViewHas('unpaidTotal', 90);
    }

    public function test_legacy_month_filter_still_selects_a_full_month(): void
    {
        [$owner, $store] = $this->createStoreOwner();

        $this->createOrder($store, 'MONTH-START', '2026-07-01 00:00:00', 'paid', 12);
        $this->createOrder($store, 'MONTH-END', '2026-07-31 23:59:59', 'paid', 18);
        $this->createOrder($store, 'MONTH-OUTSIDE', '2026-08-01 00:00:00', 'paid', 7);

        $this->actingAs($owner)
            ->get(route('store.dashboard', ['financial_month' => '2026-07']))
            ->assertOk()
            ->assertViewHas('periodSales', 30)
            ->assertViewHas('periodOrders', 2);
    }

    private function createStoreOwner(): array
    {
        $owner = User::factory()->create();
        $owner->assignRole(Role::firstOrCreate(['name' => 'store', 'guard_name' => 'web']));

        $store = Store::create([
            'name' => 'Restaurante Teste',
            'phone' => '11999999999',
            'address' => 'Rua Teste, 1',
            'document' => fake()->numerify('##############'),
            'user_id' => $owner->id,
        ]);

        return [$owner, $store];
    }

    private function createOrder(Store $store, string $number, string $createdAt, string $paymentStatus, float $total): void
    {
        $order = Order::create([
            'order_number' => $number,
            'store_id' => $store->id,
            'table_id' => null,
            'status' => 'Finalizado',
            'payment_status' => $paymentStatus,
            'total' => $total,
        ]);

        DB::table('orders')->where('id', $order->id)->update([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
