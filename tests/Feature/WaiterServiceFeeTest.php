<?php

namespace Tests\Feature;

use App\Enums\TableAssignmentType;
use App\Enums\TableServiceStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductIngredient;
use App\Models\TableParticipant;
use App\Models\Store;
use App\Models\Table;
use App\Models\TableUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WaiterServiceFeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_fee_preview_is_not_persisted_until_payment_is_marked_paid(): void
    {
        [$waiter, $table, $order] = $this->createAssignedTableWithPendingOrder();

        $this->actingAs($waiter)
            ->postJson(route('waiter.table.service-fee', $table), ['include_service_fee' => false])
            ->assertOk()
            ->assertJson([
                'consumption' => 75,
                'service_fee' => 7.5,
                'total' => 75,
            ]);

        $this->postJson(route('waiter.table.service-fee', $table), ['include_service_fee' => true])
            ->assertOk()
            ->assertJson([
                'consumption' => 75,
                'service_fee' => 7.5,
                'total' => 82.5,
            ]);

        $this->assertDatabaseCount('payments', 0);

        $this->postJson(route('waiter.table.mark-paid', $table), ['include_service_fee' => true])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'amount' => '82.50',
                'service_fee' => '7.50',
            ]);

        $this->assertDatabaseHas('payments', [
            'store_id' => $table->store_id,
            'table_id' => $table->id,
            'amount' => 82.50,
            'service_fee_included' => true,
            'service_fee_percentage' => 10,
            'service_fee' => 7.50,
            'status' => Payment::STATUS_SUCCEEDED,
        ]);
        $this->assertSame(Order::PAYMENT_STATUS_PAID, $order->fresh()->payment_status);
        $this->assertSame('75.00', $order->fresh()->total);
    }

    public function test_waiter_order_details_show_item_notes_and_selected_ingredients(): void
    {
        [$waiter, $table, $order] = $this->createAssignedTableWithPendingOrder();
        $participant = TableParticipant::create([
            'table_id' => $table->id,
            'name' => 'Cliente Teste',
            'is_owner' => true,
        ]);
        $order->update(['participant_id' => $participant->id]);
        $category = Category::create([
            'name' => 'Bebidas',
            'store_id' => $table->store_id,
        ]);
        $product = Product::create([
            'name' => 'Limonada',
            'price' => 75,
            'category_id' => $category->id,
            'store_id' => $table->store_id,
            'customizable' => true,
        ]);
        $ingredient = ProductIngredient::create([
            'product_id' => $product->id,
            'name' => 'Limão',
            'amount_item' => 1,
            'additional_price' => 0,
        ]);
        $order->update(['notes' => 'Servir sem canudo']);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 75,
            'notes' => json_encode([
                'notes' => 'Pouco gelo',
                'selected_ingredients' => [['id' => $ingredient->id, 'selected_amount' => 3]],
            ]),
        ]);

        $this->actingAs($waiter)
            ->getJson('/api/orders/' . $order->id)
            ->assertOk()
            ->assertJsonPath('order.notes', 'Servir sem canudo')
            ->assertJsonPath('order.items.0.notes', 'Pouco gelo')
            ->assertJsonPath('order.items.0.selected_ingredients.0.name', 'Limão')
            ->assertJsonPath('order.items.0.selected_ingredients.0.difference', 2);

        $this->get(route('waiter.table-details', $table))
            ->assertOk()
            ->assertSee('Pouco gelo')
            ->assertSee('Adicionado:')
            ->assertSee('Limão x2')
            ->assertSee('Servir sem canudo')
            ->assertDontSee('selected_ingredients');
    }

    private function createAssignedTableWithPendingOrder(): array
    {
        $owner = User::factory()->create();
        $store = Store::create([
            'name' => 'Restaurante Teste',
            'phone' => '11999999999',
            'address' => 'Rua Teste, 1',
            'document' => fake()->numerify('##############'),
            'user_id' => $owner->id,
        ]);

        $waiter = User::factory()->create(['store_id' => $store->id]);
        $waiter->assignRole(Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web']));

        $table = Table::create([
            'number' => '2',
            'qr_code' => 'service-fee-table',
            'store_id' => $store->id,
            'occupied' => true,
            'occupied_at' => now()->subMinute(),
        ]);

        TableUser::create([
            'store_id' => $store->id,
            'table_id' => $table->id,
            'user_id' => $waiter->id,
            'service_status' => TableServiceStatus::Active,
            'assignment_type' => TableAssignmentType::Manual,
        ]);

        $order = Order::create([
            'order_number' => 'SERVICE-FEE-1',
            'store_id' => $store->id,
            'table_id' => $table->id,
            'status' => 'Aguardando produção',
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'total' => 75,
        ]);

        return [$waiter, $table, $order];
    }
}
