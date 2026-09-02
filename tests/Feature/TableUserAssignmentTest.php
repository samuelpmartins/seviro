<?php

namespace Tests\Feature;

use App\Enums\TableServiceStatus;
use App\Models\Store;
use App\Models\Table;
use App\Models\TableUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TableUserAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigns_the_available_user_with_the_lowest_active_table_count(): void
    {
        $store = $this->createStore();
        [$firstUser, $secondUser] = $this->createAttendingUsers($store);
        $firstTable = $this->createTable($store, 'first');
        $secondTable = $this->createTable($store, 'second');
        $thirdTable = $this->createTable($store, 'third');

        $this->postJson('/api/table/create-password', ['qr_code' => $firstTable->qr_code, 'name' => 'Ana'])
            ->assertOk();
        $this->postJson('/api/table/create-password', ['qr_code' => $secondTable->qr_code, 'name' => 'Bruno'])
            ->assertOk();
        $this->postJson('/api/table/create-password', ['qr_code' => $thirdTable->qr_code, 'name' => 'Carla'])
            ->assertOk();

        $this->assertDatabaseHas('table_users', [
            'table_id' => $firstTable->id,
            'user_id' => $firstUser->id,
            'service_status' => TableServiceStatus::Active->value,
        ]);
        $this->assertDatabaseHas('table_users', [
            'table_id' => $secondTable->id,
            'user_id' => $secondUser->id,
            'service_status' => TableServiceStatus::Active->value,
        ]);
        $this->assertDatabaseHas('table_users', [
            'table_id' => $thirdTable->id,
            'user_id' => $firstUser->id,
            'service_status' => TableServiceStatus::Active->value,
        ]);
    }

    public function test_does_not_create_an_assignment_without_an_attending_user(): void
    {
        $store = $this->createStore();
        [$user] = $this->createAttendingUsers($store, false);
        $table = $this->createTable($store, 'unassigned');

        $this->postJson('/api/table/create-password', ['qr_code' => $table->qr_code, 'name' => 'Ana'])
            ->assertOk();

        $this->assertDatabaseMissing('table_users', ['table_id' => $table->id]);
        $this->assertFalse($user->fresh()->is_attending);
    }

    private function createStore(): Store
    {
        $owner = User::factory()->create();

        return Store::create([
            'name' => 'Restaurante Teste',
            'phone' => '11999999999',
            'address' => 'Rua Teste, 1',
            'document' => fake()->numerify('##############'),
            'user_id' => $owner->id,
        ]);
    }

    private function createAttendingUsers(Store $store, bool $attending = true): array
    {
        $waiterRole = Role::firstOrCreate(['name' => 'waiter']);
        $users = User::factory()->count(2)->create([
            'store_id' => $store->id,
            'is_attending' => $attending,
        ]);

        $users->each->assignRole($waiterRole);

        return $users->all();
    }

    private function createTable(Store $store, string $suffix): Table
    {
        return Table::create([
            'number' => $suffix,
            'qr_code' => 'table-' . $suffix,
            'store_id' => $store->id,
        ]);
    }
}
