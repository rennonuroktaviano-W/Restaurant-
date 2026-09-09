<?php

namespace App\Services;

use App\Events\PaymentSettled;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\PaymentGateway\GatewayManager;
use App\PaymentGateway\PaymentGateway;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentService
{
    public function __construct(protected GatewayManager $gateways) {}

    public function createOnlineAttempt(Order $order, PaymentMethod $method, ?string $idempotencyKey = null): Payment
    {
        $this->assertSettlable($order);

        return DB::transaction(function () use ($order, $method, $idempotencyKey) {
            if ($idempotencyKey) {
                $existing = Payment::where('idempotency_key', $idempotencyKey)->first();

                if ($existing) {
                    return $existing;
                }
            }

            return Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $method->id,
                'type' => 'online',
                'status' => Payment::STATUS_PENDING,
                'amount' => $order->grand_total,
                'provider' => $method->config['provider'] ?? 'mock',
                'idempotency_key' => $idempotencyKey,
                'created_by' => auth()->id() ?: $order->created_by,
            ]);
        });
    }

    /**
     * Cash payment confirmation (FR-CAS-004, BR-008).
     */
    public function confirmCash(Order $order, PaymentMethod $method, float $received, ?int $cashierId = null): Payment
    {
        $this->assertSettlable($order);

        if ($received < 0) {
            throw new RuntimeException('Uang diterima tidak valid');
        }

        if ($method->type !== 'cash' && ! auth()->user()?->hasPermissionTo('payment.override')) {
            throw new RuntimeException('Hanya metode tunai yang dapat dikonfirmasi manual tanpa override');
        }

        $change = round($received - (float) $order->grand_total, 2);

        if ($change < 0 && $method->type === 'cash') {
            throw new RuntimeException('Uang diterima kurang dari total');
        }

        return DB::transaction(function () use ($order, $method, $received, $change, $cashierId) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $method->id,
                'type' => $method->type,
                'status' => Payment::STATUS_PAID,
                'amount' => $order->grand_total,
                'amount_received' => $received,
                'change_due' => max(0, $change),
                'provider' => 'cash',
                'created_by' => $cashierId ?? auth()->id(),
                'paid_at' => now(),
            ]);

            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
            ]);

            return $payment;
        });
    }

    /**
     * Mark an online payment paid from a verified callback/inquiry (FR-PAY-003/004).
     */
    public function settleOnline(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $payment->refresh();

            if ($payment->status === Payment::STATUS_PAID) {
                return $payment;
            }

            if ($payment->status !== Payment::STATUS_PENDING) {
                throw new RuntimeException('Pembayaran tidak dalam status pending');
            }

            if ($payment->expires_at !== null && $payment->expires_at->isPast()) {
                throw new RuntimeException('Pembayaran sudah kedaluwarsa. Silakan buat attempt baru.');
            }

            if ($payment->order->isTerminal()) {
                throw new RuntimeException('Order tidak dapat dibayar pada status ini');
            }

            $payment->update([
                'status' => Payment::STATUS_PAID,
                'paid_at' => now(),
                'failure_code' => null,
            ]);

            $payment->order->update(['payment_status' => Order::PAYMENT_PAID]);

            PaymentSettled::dispatch($payment->fresh());

            return $payment->fresh();
        });
    }

    public function failOnline(Payment $payment, ?string $failureCode = null): Payment
    {
        return DB::transaction(function () use ($payment, $failureCode) {
            if ($payment->status !== Payment::STATUS_PENDING) {
                return $payment;
            }

            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failure_code' => $failureCode,
            ]);

            return $payment->fresh();
        });
    }

    public function expire(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            if (! in_array($payment->status, [Payment::STATUS_PENDING, Payment::STATUS_FAILED], true)) {
                return $payment;
            }

            $payment->update(['status' => Payment::STATUS_EXPIRED]);

            return $payment->fresh();
        });
    }

    public function gateway(Payment $payment): PaymentGateway
    {
        return $this->gateways->driver($payment->provider);
    }

    private function assertSettlable(Order $order): void
    {
        if (in_array($order->payment_status, [
            Order::PAYMENT_PAID,
            Order::PAYMENT_REFUNDED,
            Order::PAYMENT_PARTIALLY_REFUNDED,
        ], true)) {
            throw new RuntimeException('Order sudah lunas atau di-refund');
        }

        if ($order->isTerminal()) {
            throw new RuntimeException('Order tidak dapat dibayar pada status ini');
        }
    }
}
