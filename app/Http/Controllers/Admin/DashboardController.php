<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalStores = Store::count();
        $totalRevenue = Order::where('payment_status', Order::PAYMENT_STATUS_PAID)->sum('total');
        $totalFinishedOrders = Order::where('payment_status', Order::PAYMENT_STATUS_PAID)->count();
        $totalOrders = Order::count();

        $stores = Store::with('user')
            ->withCount([
                'orders as paid_orders_count' => function ($query) {
                    $query->where('payment_status', Order::PAYMENT_STATUS_PAID);
                },
                'orders as total_orders_count'
            ])
            ->withSum(['orders as paid_revenue' => function ($query) {
                $query->where('payment_status', Order::PAYMENT_STATUS_PAID);
            }], 'total')
            ->orderByDesc('paid_revenue')
            ->paginate(20);

        return view('admin.dashboard', compact(
            'totalStores',
            'totalRevenue',
            'totalFinishedOrders',
            'totalOrders',
            'stores'
        ));
    }
}
