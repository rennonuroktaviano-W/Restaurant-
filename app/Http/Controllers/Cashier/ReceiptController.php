<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Order $order): View
    {
        Gate::authorize('order.view');

        $order->load(['items', 'table', 'room', 'area', 'payments.paymentMethod', 'creator']);

        return view('cashier.receipt', compact('order'));
    }

    public function print(Order $order): View
    {
        Gate::authorize('order.view');

        $order->load(['items', 'table', 'room', 'area', 'payments.paymentMethod', 'creator']);

        return view('cashier.receipt-print', compact('order'));
    }
}
