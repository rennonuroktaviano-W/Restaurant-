<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AuditLogger;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(Order $order): View
    {
        $order->load(['items', 'table', 'room', 'area', 'payments.paymentMethod', 'statusHistories.actor']);

        $latestPendingPayment = $order->payments()
            ->with('paymentMethod')
            ->whereIn('status', ['pending', 'failed'])
            ->orderByDesc('id')
            ->first();

        return view('customer.tracking', compact('order', 'latestPendingPayment'));
    }

    public function cancel(Request $request, Order $order, OrderStatusService $status, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate(['reason' => 'required|string|max:255']);

        if ($order->payment_status === Order::PAYMENT_PAID) {
            return back()->with('error', 'Order yang sudah lunas tidak dapat dibatalkan dari halaman ini.');
        }

        if (! in_array($order->order_status, [Order::STATUS_NEW, Order::STATUS_ACCEPTED], true)) {
            return back()->with('error', 'Order tidak dapat dibatalkan pada status ini.');
        }

        try {
            $status->transition($order, Order::STATUS_CANCELLED, null, $validated['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $audit->log('cancel', 'order', 'order', $order->id, [], ['order_status' => 'cancelled']);

        return back()->with('success', "Order {$order->order_number} dibatalkan.");
    }
}
