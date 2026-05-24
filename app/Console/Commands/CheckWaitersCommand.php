<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckWaitersCommand extends Command
{
    protected $signature = 'check:waiters';
    protected $description = 'Verifica se há garçons cadastrados';

    public function handle()
    {
        $waiters = User::whereHas('roles', function($q) {
            $q->where('name', 'waiter');
        })->get();

        $this->info("Total de garçons cadastrados: " . $waiters->count());
        
        foreach ($waiters as $waiter) {
            $this->info("  ID: {$waiter->id} | Nome: {$waiter->name} | Store: {$waiter->store_id}");
        }

        if ($waiters->isEmpty()) {
            $this->warn("Nenhum garçom cadastrado!");
        }

        return 0;
    }
}
