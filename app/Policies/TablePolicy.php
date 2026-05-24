<?php

namespace App\Policies;

use App\Models\Table;
use App\Models\User;

class TablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('store');
    }

    public function view(User $user, Table $table): bool
    {
        return $user->store->id === $table->store_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('store');
    }

    public function update(User $user, Table $table): bool
    {
        return $user->store->id === $table->store_id;
    }

    public function delete(User $user, Table $table): bool
    {
        return $user->store->id === $table->store_id;
    }
} 