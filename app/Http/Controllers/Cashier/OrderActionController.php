<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Requests\CashConfirmRequest;
use App\Http\Requests\CashOnlineRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\AuditLogger;
use App\Services\OrderStatusService;
use App\Services\PaymentService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class OrderActionController extends Controller
{
    public function __construct(
        protected OrderStatusService $status,
        protected PaymentService $payments,
        protected SettingsService $settings,
        protected AuditLogger $audit,
    ) {}

    public function accept(Order $order): RedirectResponse
    {
        Gate::authorize('order.accept');

        $payLater = (bool) $this->settings->get('pricing.pay_later', false);

        if ($order->payment_status === Order::PAYMENT_PENDING && $order->payments()->where('type', 'online')->exists() && ! $payLater) {
            return back()->with('error', 'Order online belum lunas. Verifikasi pembayaran terlebih dahulu.');
        }

        try {
            $this->status->transition($order, Order::STATUS_ACCEPTED, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('accept', 'order', 'order', $order->id, ['order_status' => 'new'], ['order_status' => 'accepted']);

        return back()->with('success', "Order {$order->order_number} diterima.");
    }

    public function complete(Order $order): RedirectResponse
    {
        Gate::authorize('order.update_status');

        try {
            $this->status->transition($order, Order::STATUS_COMPLETED, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('complete', 'order', 'order', $order->id, [], ['order_status' => 'completed']);

        return back()->with('success', "Order {$order->order_number} selesai.");
    }

    public function cancel(CancelOrderRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->status->transition($order, Order::STATUS_CANCELLED, auth()->id(), $request->reason, $request->reason_code);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('cancel', 'order', 'order', $order->id, [], ['order_status' => 'cancelled'], auth()->id());

        return redirect()->route('cashier.dashboard')->with('success', "Order {$order->order_number} dibatalkan.");
    }

    public function payCash(CashConfirmRequest $request, Order $order): RedirectResponse
    {
        $method = PaymentMethod::findOrFail($request->payment_method_id);

        try {
            $payment = $this->payments->confirmCash($order, $method, (float) $request->amount_received, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('pay_cash', 'payment', 'order', $order->id, [], ['payment' => $payment->id, 'amount' => $payment->amount]);

        return redirect()->route('cashier.receipt.show', $order)->with('success', 'Pembayaran tunai berhasil.');
    }

    public function payOnline(CashOnlineRequest $request, Order $order): RedirectResponse
    {
        $method = PaymentMethod::findOrFail($request->payment_method_id);

        if ($method->type !== 'online') {
            return back()->with('error', 'Metode tersebut bukan pembayaran online.');
        }

        $amount = $method->code === 'qris' ? (float) $request->amount : null;

        try {
            $payment = $this->payments->createOnlineAttempt($order, $method, 'order-'.$order->id.'-attempt-'.now()->format('YmdHis'), $amount);
            $initiate = $this->payments->gateway($payment)->initiate($payment);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->away($initiate['redirect']);
    }

    public function confirmQris(Order $order): RedirectResponse
    {
        Gate::authorize('payment.initiate');

        $payment = $order->payments()
            ->where('provider', 'qris')
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

        if (! $payment) {
            return back()->with('error', 'Tidak ada pembayaran QRIS yang menunggu konfirmasi.');
        }

        try {
            $payment = $this->payments->confirmQris($payment, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('confirm_qris', 'payment', 'order', $order->id, [], ['payment' => $payment->id, 'amount' => $payment->amount]);

        return redirect()->route('cashier.orders.show', $order)->with('success', 'Pembayaran QRIS dikonfirmasi lunas.');
    }
}
