<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $storeRole = Role::firstOrCreate(['name' => 'store', 'guard_name' => 'web']);

        // Criar permissões
        $permissions = [
            // Permissões de administrador
            'manage stores',

            // Permissões de loja
            'manage store',
            'manage categories',
            'manage products',
            'manage tables',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Atribuir permissões aos roles
        $adminRole->givePermissionTo('manage stores');

        $storeRole->givePermissionTo([
            'manage store',
            'manage categories',
            'manage products',
            'manage tables'
        ]);
    }
}
