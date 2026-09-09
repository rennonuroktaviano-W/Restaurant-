<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('report.view');
        $monthStart = now()->startOfMonth();

        $grossSales = (float) Order::where('order_status', Order::STATUS_COMPLETED)
            ->where('completed_at', '>=', $monthStart)
            ->sum('grand_total');

        $orderCount = Order::where('order_status', Order::STATUS_COMPLETED)
            ->where('completed_at', '>=', $monthStart)
            ->count();

        $avgOrderValue = $orderCount > 0 ? round($grossSales / $orderCount, 2) : 0;

        $statusDistribution = Order::query()
            ->select('order_status', DB::raw('count(*) as total'))
            ->where('ordered_at', '>=', $monthStart)
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_status', Order::STATUS_COMPLETED)
            ->where('orders.completed_at', '>=', $monthStart)
            ->select('order_items.product_name', DB::raw('sum(order_items.quantity) as qty'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $paymentMix = Payment::query()
            ->join('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
            ->where('payments.status', Payment::STATUS_PAID)
            ->where('payments.paid_at', '>=', $monthStart)
            ->select('payment_methods.name', DB::raw('sum(payments.amount) as total'))
            ->groupBy('payment_methods.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $recentOrders = Order::with('table', 'room')
            ->where('ordered_at', '>=', now()->subDays(7))
            ->orderByDesc('ordered_at')
            ->limit(10)
            ->get();

        $lowStock = Product::where('stock_type', 'limited')
            ->where('stock', '<=', 10)
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'grossSales',
            'orderCount',
            'avgOrderValue',
            'statusDistribution',
            'topProducts',
            'paymentMix',
            'recentOrders',
            'lowStock',
        ));
    }
}
