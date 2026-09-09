<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    private function paidOrder(float $total): Order
    {
        $order = Order::factory()->takeAway()->completed()->create([
            'subtotal' => $total,
            'grand_total' => $total,
            'payment_status' => Order::PAYMENT_PAID,
            'completed_at' => now(),
        ]);

        $method = PaymentMethod::factory()->create(['type' => 'cash']);

        $order->payments()->create([
            'payment_method_id' => $method->id,
            'type' => 'cash',
            'status' => Payment::STATUS_PAID,
            'amount' => $total,
            'provider' => 'cash',
            'paid_at' => now(),
        ]);

        return $order;
    }

    public function test_admin_can_issue_full_refund(): void
    {
        $order = $this->paidOrder(100000);

        $this->actAsFresh($this->adminUser());

        $this->post(route('admin.refunds.store', $order->payments->first()), [
            'amount' => 100000,
            'reason_code' => 'customer',
            'reason' => 'Pelanggan membatalkan pesanan',
        ])->assertRedirect(route('admin.refunds.index'));

        $this->assertDatabaseHas('refunds', [
            'order_id' => $order->id,
            'amount' => 100000,
            'status' => Refund::STATUS_SUCCEEDED,
        ]);

        $payment = $order->payments->first()->fresh();
        $this->assertSame(Payment::STATUS_REFUNDED, $payment->status);
        $this->assertSame(Order::PAYMENT_REFUNDED, $order->fresh()->payment_status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'refund',
            'module' => 'payment',
            'target_type' => 'order',
            'target_id' => $order->id,
        ]);
    }

    public function test_partial_refund_marks_order_partially_refunded(): void
    {
        $order = $this->paidOrder(100000);

        $this->actAsFresh($this->adminUser());

        $this->post(route('admin.refunds.store', $order->payments->first()), [
            'amount' => 40000,
            'reason_code' => 'other',
            'reason' => 'Kompensasi sebagian',
        ])->assertRedirect(route('admin.refunds.index'));

        $payment = $order->payments->first()->fresh();
        $this->assertSame(Payment::STATUS_PARTIALLY_REFUNDED, $payment->status);
        $this->assertSame(Order::PAYMENT_PARTIALLY_REFUNDED, $order->fresh()->payment_status);

        $this->assertDatabaseCount('refunds', 1);
        $this->assertSame(40000.0, (float) $order->fresh()->refunds()->sum('amount'));
    }

    public function test_refund_amount_cannot_exceed_paid_amount(): void
    {
        $order = $this->paidOrder(50000);

        $this->actAsFresh($this->adminUser());

        $this->post(route('admin.refunds.store', $order->payments->first()), [
            'amount' => 1000000,
            'reason' => 'Coba refund berlebihan',
        ])->assertSessionHasErrors('amount');
    }

    public function test_refund_requires_reason(): void
    {
        $order = $this->paidOrder(50000);

        $this->actAsFresh($this->adminUser());

        $this->post(route('admin.refunds.store', $order->payments->first()), [
            'amount' => 50000,
            'reason' => '',
        ])->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('refunds', 0);
    }

    public function test_cashier_cannot_access_refund(): void
    {
        $order = $this->paidOrder(50000);

        $this->actAsFresh($this->cashierUser());

        $this->get(route('admin.refunds.index'))->assertForbidden();
        $this->get(route('admin.refunds.create', $order->payments->first()))->assertForbidden();
        $this->post(route('admin.refunds.store', $order->payments->first()), [
            'amount' => 50000,
            'reason' => 'Tidak sah',
        ])->assertForbidden();

        $this->assertDatabaseCount('refunds', 0);
    }

    public function test_manager_can_issue_refund(): void
    {
        $order = $this->paidOrder(75000);

        $this->actAsFresh($this->managerUser());

        $this->post(route('admin.refunds.store', $order->payments->first()), [
            'amount' => 75000,
            'reason' => 'Kesalahan kasir',
        ])->assertRedirect(route('admin.refunds.index'));

        $this->assertSame(Order::PAYMENT_REFUNDED, $order->fresh()->payment_status);
    }

    public function test_refund_deducts_from_net_sales(): void
    {
        $order = $this->paidOrder(100000);

        $this->actAsFresh($this->adminUser());
        $this->post(route('admin.refunds.store', $order->payments->first()), [
            'amount' => 30000,
            'reason' => 'Sebagian',
        ])->assertRedirect();

        $this->get(route('admin.reports.index', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertViewHas('grossSales', 100000.0)
            ->assertViewHas('refundTotal', 30000.0)
            ->assertViewHas('netSales', 70000.0);
    }

    public function test_refunded_order_cannot_accept_new_payment(): void
    {
        $order = $this->paidOrder(100000);
        $method = $order->payments->first()->paymentMethod;

        $this->actAsFresh($this->adminUser());
        $this->post(route('admin.refunds.store', $order->payments->first()), [
            'amount' => 100000,
            'reason' => 'Refund penuh',
        ])->assertRedirect();

        $this->expectException(\RuntimeException::class);
        app(PaymentService::class)->confirmCash($order->fresh(), $method, 100000);
    }
}
