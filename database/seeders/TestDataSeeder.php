<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Store;
use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Criar roles se não existirem
        if (!Role::where('name', 'store')->exists()) {
            Role::create(['name' => 'store']);
        }

        // Criar usuário de teste
        $user = User::firstOrCreate(
            ['email' => 'restaurante@teste.com'],
            [
                'name' => 'Restaurante Teste',
                'password' => Hash::make('password'),
            ]
        );

        // Atribuir role de store
        if (!$user->hasRole('store')) {
            $user->assignRole('store');
        }

        // Criar loja
        $store = Store::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Restaurante Teste',
                'phone' => '(11) 99999-9999',
                'address' => 'Rua Teste, 123',
                'document' => '12345678901234',
            ]
        );

        // Criar categoria
        $category = Category::firstOrCreate(
            ['name' => 'Pratos Principais', 'store_id' => $store->id],
            [
                'icon' => 'fas fa-utensils',
                'order' => 1,
            ]
        );

        // Criar produto
        $product = Product::firstOrCreate(
            ['name' => 'Hambúrguer', 'store_id' => $store->id],
            [
                'description' => 'Hambúrguer artesanal com queijo',
                'price' => 25.90,
                'category_id' => $category->id,
                'active' => true,
                'order' => 1,
            ]
        );

        // Criar mesa com QR code específico
        $table = Table::firstOrCreate(
            ['number' => 1, 'store_id' => $store->id],
            [
                'qr_code' => 'Bd5TaP24ocqHMBIoHWXrGdZW7TYTHAut',
            ]
        );

        $this->command->info('✅ Dados de teste criados!');
        $this->command->info('');
        $this->command->info('📧 Email: restaurante@teste.com');
        $this->command->info('🔑 Senha: password');
        $this->command->info('');
        $this->command->info('🔗 URL do Cardápio: ' . url('/menu/Bd5TaP24ocqHMBIoHWXrGdZW7TYTHAut'));
    }
}
