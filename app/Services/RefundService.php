<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RefundService
{
    public function __construct(protected AuditLogger $audit) {}

    /**
     * Record a refund against a settled payment (FR-PAY-005).
     *
     * @return array{refund: Refund, payment_status: string}
     */
    public function refund(Order $order, Payment $payment, float $amount, string $reason, ?int $actorId = null, ?string $reasonCode = null): array
    {
        if ($payment->order_id !== $order->id) {
            throw new RuntimeException('Payment tidak terkait dengan order ini');
        }

        if ($payment->status !== Payment::STATUS_PAID) {
            throw new RuntimeException('Hanya payment yang sudah lunas yang dapat di-refund');
        }

        if ($amount <= 0) {
            throw new RuntimeException('Jumlah refund harus lebih dari 0');
        }

        if (blank($reason)) {
            throw new RuntimeException('Alasan refund wajib diisi');
        }

        return DB::transaction(function () use ($order, $payment, $amount, $reason, $actorId, $reasonCode) {
            $refundedTotal = (float) $payment->refunds()
                ->where('status', Refund::STATUS_SUCCEEDED)
                ->sum('amount');

            if ($refundedTotal + $amount > (float) $payment->amount) {
                throw new RuntimeException('Jumlah refund melebihi total payment');
            }

            $refund = Refund::create([
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount' => $amount,
                'reason_code' => $reasonCode,
                'reason' => $reason,
                'status' => Refund::STATUS_SUCCEEDED,
                'created_by' => $actorId ?? auth()->id(),
            ]);

            $paymentStatus = $refundedTotal + $amount >= (float) $payment->amount
                ? Payment::STATUS_REFUNDED
                : Payment::STATUS_PARTIALLY_REFUNDED;

            $payment->update(['status' => $paymentStatus]);

            $orderStatus = $this->resolveOrderPaymentStatus($paymentStatus, $order);

            $order->update(['payment_status' => $orderStatus]);

            $this->audit->log(
                'refund',
                'payment',
                'order',
                $order->id,
                [
                    'payment' => $payment->id,
                    'amount' => $payment->amount,
                ],
                [
                    'refund' => $refund->id,
                    'amount' => $refund->amount,
                    'payment_status' => $paymentStatus,
                ],
                $actorId,
            );

            return ['refund' => $refund, 'payment_status' => $paymentStatus];
        });
    }

    private function resolveOrderPaymentStatus(string $paymentStatus, Order $order): string
    {
        if ($paymentStatus === Payment::STATUS_PARTIALLY_REFUNDED) {
            return Order::PAYMENT_PARTIALLY_REFUNDED;
        }

        $hasOtherPaid = Payment::query()
            ->where('order_id', $order->id)
            ->where('status', Payment::STATUS_PAID)
            ->exists();

        return $hasOtherPaid ? Order::PAYMENT_PARTIALLY_REFUNDED : Order::PAYMENT_REFUNDED;
    }
}
