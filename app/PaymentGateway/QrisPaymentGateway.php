<?php

namespace App\PaymentGateway;

use App\Models\Payment;
use Illuminate\Support\Str;
use RuntimeException;

class QrisPaymentGateway implements PaymentGateway
{
    public function supports(): string
    {
        return 'qris';
    }

    /**
     * Return the client redirect URL to the QRIS display page.
     * No async provider: the cashier confirms receipt manually.
     */
    public function initiate(Payment $payment): array
    {
        if ($payment->external_id === null) {
            $payment->update([
                'external_id' => 'QRIS-'.strtoupper(Str::uuid()->toString()),
                'expires_at' => null,
            ]);
        }

        return [
            'redirect' => route('payment.qris.pay', ['payment' => $payment->id]),
        ];
    }

    public function handleWebhook(array $payload): Payment
    {
        throw new RuntimeException('QRIS payments are confirmed manually by the cashier.');
    }

    public function inquiry(Payment $payment): Payment
    {
        return $payment;
    }
}
