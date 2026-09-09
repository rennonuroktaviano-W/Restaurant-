<?php

namespace Tests\Feature;

use App\Events\PaymentSettled;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\PaymentGateway\GatewayManager;
use App\Services\OrderStatusService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ConcurrencyHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    private function product(array $attributes = []): Product
    {
        return Product::factory()->create($attributes + ['stock_type' => 'unlimited', 'stock' => 99]);
    }

    private function addToCart(int $productId): void
    {
        $this->post(route('cart.add'), [
            'product_id' => $productId,
            'quantity' => 1,
        ])->assertRedirect();
    }

    private function onlineMethod(): PaymentMethod
    {
        return PaymentMethod::factory()->create(['type' => 'online']);
    }

    private function order(): Order
    {
        return Order::factory()->takeAway()->create();
    }

    private function pendingPayment(Order $order, PaymentMethod $method, string $externalId, ?\DateTime $expiresAt = null): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'type' => 'online',
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->grand_total,
            'provider' => 'mock',
            'external_id' => $externalId,
            'expires_at' => $expiresAt,
        ]);
    }

    private function signedPaidPayload(Payment $payment): array
    {
        $gateway = app(GatewayManager::class)->driver('mock');

        $payload = [
            'event' => 'payment.paid',
            'external_id' => $payment->external_id,
            'amount' => (string) $payment->amount,
        ];

        $payload['signature'] = $gateway->sign($payload);

        return $payload;
    }

    public function test_ac_04_duplicate_webhook_is_idempotent_and_settles_once(): void
    {
        Event::fake([PaymentSettled::class]);

        $order = $this->order();
        $payment = $this->pendingPayment($order, $this->onlineMethod(), 'EXT-DUP-1');
        $payload = $this->signedPaidPayload($payment);

        $this->postJson(route('webhook.payment.mock'), $payload)->assertOk();
        $this->postJson(route('webhook.payment.mock'), $payload)->assertOk();

        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        $this->assertSame(Order::PAYMENT_PAID, $order->fresh()->payment_status);
        Event::assertDispatched(PaymentSettled::class, 1);
    }

    public function test_late_webhook_after_expiry_does_not_mark_paid(): void
    {
        $order = $this->order();
        $payment = $this->pendingPayment($order, $this->onlineMethod(), 'EXT-LATE-1', now()->subMinute());

        $this->postJson(route('webhook.payment.mock'), $this->signedPaidPayload($payment))
            ->assertStatus(400);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    public function test_webhook_cannot_settle_a_cancelled_order(): void
    {
        $order = $this->order();
        $payment = $this->pendingPayment($order, $this->onlineMethod(), 'EXT-CANC-1');

        app(OrderStatusService::class)->transition($order, Order::STATUS_CANCELLED, null, 'Dibatalkan admin');

        $this->postJson(route('webhook.payment.mock'), $this->signedPaidPayload($payment))
            ->assertStatus(400);

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->order_status);
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    public function test_fr_pay_002_only_one_active_settlement_per_order(): void
    {
        $method = $this->onlineMethod();
        $order = $this->order();
        $payment = $this->pendingPayment($order, $method, 'EXT-ONLY-1');

        app(PaymentService::class)->settleOnline($payment);

        $this->assertSame(Order::PAYMENT_PAID, $order->refresh()->payment_status);

        $this->expectException(\RuntimeException::class);

        app(PaymentService::class)->createOnlineAttempt($order, $method, 'attempt-lagi');
    }

    public function test_fr_pay_006_retry_creates_new_attempt_keeping_failed_one(): void
    {
        $method = $this->onlineMethod();
        $order = $this->order();
        $first = $this->pendingPayment($order, $method, 'EXT-RETRY-1');

        app(PaymentService::class)->failOnline($first, 'DECLINED');

        $second = app(PaymentService::class)->createOnlineAttempt($order, $method, 'retry-key-1');

        $this->assertSame(Payment::STATUS_PENDING, $second->status);
        $this->assertSame(Payment::STATUS_FAILED, $first->fresh()->status);
        $this->assertSame(Order::PAYMENT_PENDING, $order->fresh()->payment_status);
    }

    public function test_ac_04_duplicate_checkout_with_same_idempotency_key_returns_single_order(): void
    {
        $product = $this->product();
        $this->addToCart($product->id);

        $payload = [
            'order_type' => Order::TYPE_TAKE_AWAY,
            'idempotency_key' => 'checkout-dup-1',
        ];

        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $this->addToCart($product->id);
        $this->post(route('checkout.store'), $payload)->assertRedirect();

        $this->assertSame(1, Order::count());
        $this->assertSame(1, Order::firstOrFail()->items()->count());
    }

    public function test_orders_expire_payments_command_expires_overdue_pending_payments(): void
    {
        $method = $this->onlineMethod();
        $overdueOrder = $this->order();
        $futureOrder = $this->order();

        $overdue = $this->pendingPayment($overdueOrder, $method, 'EXT-OVER-1', now()->subMinutes(20));
        $future = $this->pendingPayment($futureOrder, $method, 'EXT-FUT-1', now()->addHour());

        $this->artisan('orders:expire-payments')
            ->expectsOutputToContain('Expired 1')
            ->assertExitCode(0);

        $this->assertSame(Payment::STATUS_EXPIRED, $overdue->fresh()->status);
        $this->assertSame(Payment::STATUS_PENDING, $future->fresh()->status);
        $this->assertSame(Order::PAYMENT_PENDING, $overdueOrder->fresh()->payment_status);
        $this->assertFalse($overdueOrder->fresh()->isTerminal());
    }
}
