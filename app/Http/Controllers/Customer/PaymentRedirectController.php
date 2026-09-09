<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\PaymentGateway\MockPaymentGateway;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PaymentRedirectController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    public function show(Payment $payment): View
    {
        if (! in_array($payment->status, [Payment::STATUS_PENDING, Payment::STATUS_FAILED], true)) {
            return view('customer.payment-result', [
                'payment' => $payment,
                'order' => $payment->order,
            ]);
        }

        $payment->load('paymentMethod');
        $order = $payment->order;

        return view('customer.payment-mock', compact('payment', 'order'));
    }

    public function process(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'outcome' => ['required', Rule::in(['paid', 'failed', 'pending'])],
        ]);

        if (! in_array($payment->status, [Payment::STATUS_PENDING, Payment::STATUS_FAILED], true)) {
            return redirect()->route('tracking.show', $payment->order)->with('error', 'Pembayaran sudah selesai.');
        }

        // Simulate the gateway provider's async state.
        $attempts = Cache::get('mock_payment_attempts', []);
        $attempts[$payment->external_id] = $data['outcome'];
        Cache::put('mock_payment_attempts', $attempts, now()->addDay());

        if (in_array($data['outcome'], ['paid', 'failed'], true)) {
            try {
                $this->simulateWebhook($payment, $data['outcome']);
            } catch (RuntimeException $e) {
                return redirect()->route('tracking.show', $payment->order)->with('error', 'Simulasi pembayaran gagal: '.$e->getMessage());
            }
        }

        if ($data['outcome'] === 'paid') {
            return redirect()->route('tracking.show', $payment->order)->with('success', 'Pembayaran berhasil.');
        }

        return redirect()->route('tracking.show', $payment->order)
            ->with($data['outcome'] === 'failed' ? 'error' : 'info', $this->message($data['outcome']));
    }

    private function simulateWebhook(Payment $payment, string $event): void
    {
        /** @var MockPaymentGateway $gateway */
        $gateway = $this->payments->gateway($payment);

        if (! $gateway instanceof MockPaymentGateway) {
            return;
        }

        $payload = [
            'event' => $event === 'paid' ? 'payment.paid' : 'payment.failed',
            'external_id' => $payment->external_id,
            'amount' => (string) $payment->amount,
        ];

        $payload['signature'] = $gateway->sign($payload);

        $gateway->handleWebhook($payload);
    }

    private function message(string $outcome): string
    {
        return match ($outcome) {
            'failed' => 'Pembayaran gagal. Silakan coba lagi.',
            default => 'Status pembayaran masih menunggu konfirmasi.',
        };
    }
}
