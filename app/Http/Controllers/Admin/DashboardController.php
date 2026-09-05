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
        $storeSearch = trim((string) $request->input('store_search', ''));
        $matchingStoreIds = null;
        if ($storeSearch !== '') {
            $matchingStoreIds = Store::query()
                ->where(function ($query) use ($storeSearch) {
                    $query->where('name', 'like', "%{$storeSearch}%")
                        ->orWhere('document', 'like', "%{$storeSearch}%");
                })
                ->pluck('id');
        }

        $ordersQuery = Order::query();
        if ($matchingStoreIds !== null) {
            $ordersQuery->whereIn('store_id', $matchingStoreIds);
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $paidOrdersQuery = (clone $ordersQuery)->where('payment_status', Order::PAYMENT_STATUS_PAID);

        $totalStores = $matchingStoreIds !== null ? $matchingStoreIds->count() : Store::count();
        $totalRevenue = (clone $paidOrdersQuery)->sum('total');
        $totalFinishedOrders = (clone $paidOrdersQuery)->count();
        $totalOrders = (clone $ordersQuery)->count();
        $monthlyRevenue = (clone $paidOrdersQuery)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('total');
        $monthlyFinishedOrders = (clone $paidOrdersQuery)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $stores = Store::with('user')
            ->when($matchingStoreIds !== null, function ($query) use ($matchingStoreIds) {
                $query->whereIn('id', $matchingStoreIds);
            })
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
            ->paginate(20)
            ->withQueryString();

        return view('admin.dashboard', compact(
            'totalStores',
            'totalRevenue',
            'totalFinishedOrders',
            'totalOrders',
            'monthlyRevenue',
            'monthlyFinishedOrders',
            'stores',
            'storeSearch'
        ));
    }
}
