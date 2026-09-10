<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\DiningTable;
use App\Models\PaymentMethod;
use App\Models\Room;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkout,
        protected CartService $cart,
        protected PaymentService $payments,
    ) {}

    public function store(CheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $key = $request->idempotency_key ?: session('checkout.key') ?: Str::uuid()->toString();

        $data = $request->safe()->only([
            'table_id', 'room_id', 'customer_name', 'customer_phone', 'notes', 'discount_code',
        ]);

        $data['area_id'] = $this->resolveAreaId($request);

        try {
            $result = $this->checkout->checkout(
                $this->cart->toCheckoutPayload(),
                $request->input('order_type'),
                $data,
                $key,
            );

            $order = $result['order'];
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        session()->forget('checkout.key');
        session()->forget('cart.discount_code');

        $this->cart->clear();

        $paymentMethod = PaymentMethod::find($request->payment_method_id);

        // Order created: customer may be auto-redirected to online payment.
        if ($paymentMethod && $paymentMethod->type === 'online') {
            try {
                $payment = $this->payments->createOnlineAttempt($order, $paymentMethod, 'checkout-'.$key);
                $redirect = $this->payments->gateway($payment)->initiate($payment)['redirect'];

                return redirect()->away($redirect);
            } catch (RuntimeException $e) {
                return redirect()->route('tracking.show', $order)->with('error', 'Gagal memulai pembayaran online: '.$e->getMessage());
            }
        }

        session(['tracking.order' => $order->order_number]);

        return redirect()->route('tracking.show', $order)
            ->with('success', "Order {$order->order_number} berhasil dibuat. Silakan lanjut ke pembayaran.");
    }

    private function resolveAreaId(CheckoutRequest $request): ?int
    {
        if ($request->filled('room_id')) {
            return Room::whereKey($request->input('room_id'))->value('area_id');
        }

        if ($request->filled('table_id')) {
            return DiningTable::whereKey($request->input('table_id'))->value('area_id');
        }

        return null;
    }
}
