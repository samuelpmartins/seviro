<?php

namespace App\Console\Commands;

use App\Models\UserAdmin;
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
        if (UserAdmin::where('email', $email)->exists()) {
            $this->error('Este email já está em uso!');
            return 1;
        }

        // Cria o usuário admin separado
        UserAdmin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'first_access' => true,
        ]);

        $this->info('Usuário admin criado com sucesso!');
        return 0;
    }
}
