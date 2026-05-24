<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Criar roles
        $adminRole = Role::create(['name' => 'admin']);
        $storeRole = Role::create(['name' => 'store']);

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
            Permission::create(['name' => $permission]);
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