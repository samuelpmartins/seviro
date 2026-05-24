<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {name} {email} {password}';
    protected $description = 'Cria um novo usuário administrador';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Verifica se o email já está em uso
        if (User::where('email', $email)->exists()) {
            $this->error('Este email já está em uso!');
            return 1;
        }

        // Cria o usuário
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Atribui o role de admin
        $user->assignRole('admin');

        $this->info('Usuário administrador criado com sucesso!');
        return 0;
    }
} 