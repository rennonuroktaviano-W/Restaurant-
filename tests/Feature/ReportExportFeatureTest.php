<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStaffRolesAndSettings();
    }

    private function admin(): static
    {
        return $this->actAsFresh($this->adminUser());
    }

    private function completedOrder(float $total, int $daysAgo): Order
    {
        $order = Order::factory()->takeAway()->completed()->create([
            'subtotal' => $total,
            'grand_total' => $total,
            'ordered_at' => now()->subDays($daysAgo),
            'completed_at' => now()->subDays($daysAgo),
        ]);

        $method = PaymentMethod::factory()->create(['type' => 'cash']);

        $order->payments()->create([
            'payment_method_id' => $method->id,
            'type' => 'cash',
            'status' => Payment::STATUS_PAID,
            'amount' => $total,
            'provider' => 'cash',
            'paid_at' => now()->subDays($daysAgo),
        ]);

        return $order;
    }

    public function test_report_aggregates_honor_date_filters(): void
    {
        $this->completedOrder(100000, 0);
        $this->completedOrder(200000, 5);

        $this->admin()->get(route('admin.reports.index', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertViewHas('grossSales', 100000.0)
            ->assertViewHas('orderCount', 1)
            ->assertViewHas('cancelledCount', 0);
    }

    public function test_ac_10_csv_export_matches_filtered_orders(): void
    {
        $todayA = $this->completedOrder(125000, 0);
        $todayB = $this->completedOrder(45800, 0);
        $this->completedOrder(999999, 5);

        $response = $this->admin()->get(route('admin.reports.export', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]))->assertOk();

        $rows = $this->csvRows($response->streamedContent());

        $this->assertCount(2, $rows);

        $orderNumbers = array_column($rows, 0);
        $this->assertContains($todayA->order_number, $orderNumbers);
        $this->assertContains($todayB->order_number, $orderNumbers);
        $this->assertNotContains('ORD-999999', $orderNumbers);

        $rowFor = fn (Order $order): array => collect($rows)->firstWhere(0, $order->order_number);
        $expectedA = number_format(125000, 2, ',', '.');
        $expectedB = number_format(45800, 2, ',', '.');

        $this->assertSame($expectedA, $rowFor($todayA)[10]);
        $this->assertSame($expectedB, $rowFor($todayB)[10]);
    }

    public function test_report_pdf_is_printable(): void
    {
        $order = $this->completedOrder(75000, 0);

        $this->admin()->get(route('admin.reports.pdf', [
            'date_from' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Rp 75.000');
    }

    public function test_cashier_cannot_export_reports(): void
    {
        $this->actAsFresh($this->cashierUser());

        $this->get(route('admin.reports.export'))
            ->assertForbidden();
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function csvRows(string $content): array
    {
        $content = ltrim($content, "\xEF\xBB\xBF");

        $lines = collect(explode("\n", trim($content)))
            ->filter(fn ($line) => $line !== '')
            ->values();

        $rows = $lines->skip(1)->map(fn ($line) => str_getcsv($line))->all();

        return $rows;
    }
}
