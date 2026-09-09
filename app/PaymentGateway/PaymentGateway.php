<?php

namespace App\PaymentGateway;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * Create a payment session/redirect. Returns the provider redirect URL and external id.
     */
    public function initiate(Payment $payment): array;

    /**
     * Verify + settle a payment based on webhook payload. Throws on invalid signature.
     */
    public function handleWebhook(array $payload): Payment;

    /**
     * Server-side inquiry to reconcile a payment (FR-PAY-004).
     */
    public function inquiry(Payment $payment): Payment;

    public function supports(): string;
}
