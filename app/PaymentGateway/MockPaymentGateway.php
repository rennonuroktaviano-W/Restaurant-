<?php

namespace App\PaymentGateway;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class MockPaymentGateway implements PaymentGateway
{
    public function __construct(protected PaymentService $paymentService) {}

    public function supports(): string
    {
        return 'mock';
    }

    /**
     * Return a client-redirect URL to the mock payment page.
     */
    public function initiate(Payment $payment): array
    {
        if ($payment->external_id === null) {
            $payment->update([
                'external_id' => 'MOCK-'.strtoupper(uniqid('', true)),
                'expires_at' => now()->addMinutes(15),
            ]);
        }

        return [
            'redirect' => route('payment.mock.pay', ['payment' => $payment->id]),
        ];
    }

    public function handleWebhook(array $payload): Payment
    {
        $this->verifySignature($payload);

        $payment = Payment::where('external_id', $payload['external_id'] ?? null)->first();

        if (! $payment) {
            throw new RuntimeException('Payment not found for external_id');
        }

        if (($payload['event'] ?? null) === 'payment.paid') {
            return $this->paymentService->settleOnline($payment);
        }

        if (($payload['event'] ?? null) === 'payment.failed') {
            return $this->paymentService->failOnline($payment, $payload['failure_code'] ?? 'DECLINED');
        }

        return $payment;
    }

    public function inquiry(Payment $payment): Payment
    {
        if ($this->mockProviderStatus($payment) === 'paid' && ! $payment->paid_at) {
            return $this->paymentService->settleOnline($payment);
        }

        return $payment;
    }

    private function mockProviderStatus(Payment $payment): string
    {
        $attempts = cache()->get('mock_payment_attempts', []);

        return $attempts[$payment->external_id] ?? 'pending';
    }

    public function sign(array $payload): string
    {
        ksort($payload);
        $data = http_build_query($payload);
        $secret = $this->mockSecret();

        return hash_hmac('sha256', $data, $secret);
    }

    private function verifySignature(array $payload): void
    {
        $signature = $payload['signature'] ?? null;
        unset($payload['signature']);

        if (! $signature || ! hash_equals($this->sign($payload), $signature)) {
            throw new RuntimeException('Invalid webhook signature');
        }
    }

    private function mockSecret(): string
    {
        return Config::get('services.mock_gateway.secret', 'mock-secret');
    }
}
