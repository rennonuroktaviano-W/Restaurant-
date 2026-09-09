<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
}
