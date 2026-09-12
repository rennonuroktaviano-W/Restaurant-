<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function dashboard(): View
    {
        Gate::authorize('order.view');

        $newOrders = Order::with(['items', 'table', 'room', 'area', 'payments'])
            ->where('order_status', Order::STATUS_NEW)
            ->where('payment_status', '!=', Order::PAYMENT_FAILED)
            ->orderBy('ordered_at')
            ->get();

        $activeOrders = Order::with(['items', 'table', 'room', 'area', 'payments'])
            ->whereIn('order_status', [Order::STATUS_ACCEPTED, Order::STATUS_COOKING])
            ->orderBy('accepted_at')
            ->orderBy('ordered_at')
            ->get();

        $readyOrders = Order::with(['items', 'table', 'room', 'area', 'payments'])
            ->where('order_status', Order::STATUS_READY)
            ->orderBy('ready_at')
            ->get();

        $methods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        return view('cashier.dashboard', compact('newOrders', 'activeOrders', 'readyOrders', 'methods'));
    }

    public function freshness(): JsonResponse
    {
        Gate::authorize('order.view');

        $new = Order::query()
            ->where('order_status', Order::STATUS_NEW)
            ->where('payment_status', '!=', Order::PAYMENT_FAILED)
            ->orderBy('ordered_at')
            ->pluck('payment_status', 'id');

        $active = Order::query()
            ->whereIn('order_status', [Order::STATUS_ACCEPTED, Order::STATUS_COOKING])
            ->orderBy('accepted_at')
            ->orderBy('ordered_at')
            ->pluck('order_status', 'id');

        $ready = Order::query()
            ->where('order_status', Order::STATUS_READY)
            ->orderBy('ready_at')
            ->pluck('payment_status', 'id');

        $signature = md5(json_encode([$new, $active, $ready]));

        return response()->json(['signature' => $signature]);
    }

    public function orders(): View
    {
        Gate::authorize('order.view');

        $orders = Order::with(['items', 'table', 'room', 'area', 'payments'])
            ->whereNotIn('order_status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->orderBy('ordered_at')
            ->get();

        return view('cashier.orders', compact('orders'));
    }

    public function show(Order $order): View
    {
        Gate::authorize('order.view');

        $order->load(['items', 'table', 'room', 'area', 'payments.paymentMethod', 'statusHistories.actor', 'cancellation.actor']);
        $methods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        return view('cashier.order-show', compact('order', 'methods'));
    }

    public function history(Request $request): View
    {
        Gate::authorize('order.view');

        $orders = Order::query()
            ->with(['table', 'room', 'area', 'payments.paymentMethod'])
            ->when($request->filled('date_from'), fn ($q, $v) => $q->whereDate('ordered_at', '>=', $v))
            ->when($request->filled('date_to'), fn ($q, $v) => $q->whereDate('ordered_at', '<=', $v))
            ->when($request->filled('status'), fn ($q, $v) => $q->where('order_status', $v))
            ->when($request->filled('order_number'), fn ($q, $v) => $q->where('order_number', 'like', "%{$v}%"))
            ->when($request->filled('payment_method_id'), fn ($q, $v) => $q->whereHas('payments', fn ($p) => $p->where('payment_method_id', $v)))
            ->whereIn('order_status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
            ->orderByDesc('ordered_at')
            ->paginate(25)
            ->withQueryString();

        $methods = PaymentMethod::where('is_active', true)->get();

        return view('cashier.history', compact('orders', 'methods'));
    }
}
