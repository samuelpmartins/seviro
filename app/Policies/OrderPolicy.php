<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function update(User $user, Order $order): bool
    {
        return optional($user->store)->id === $order->store_id
            || optional($user->workplace)->id === $order->store_id;
    }

    public function delete(User $user, Order $order): bool
    {
        return optional($user->store)->id === $order->store_id
            || optional($user->workplace)->id === $order->store_id;
    }
}
