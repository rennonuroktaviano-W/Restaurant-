<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
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

    public function test_report_pdf_is_printable(): void
    {
        $this->completedOrder(75000, 0);

        $response = $this->admin()->get(route('admin.reports.pdf', [
            'date_from' => now()->toDateString(),
        ]))->assertOk();

        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
    }

    public function test_excel_export_matches_filtered_orders(): void
    {
        $todayA = $this->completedOrder(125000, 0);
        $todayB = $this->completedOrder(45800, 0);
        $this->completedOrder(999999, 5);

        $response = $this->admin()->get(route('admin.reports.excel', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $tmp = tempnam(sys_get_temp_dir(), 'report').'.xlsx';
        file_put_contents($tmp, $response->streamedContent());

        try {
            $spreadsheet = IOFactory::createReaderForFile($tmp)->load($tmp);

            $this->assertSame(['Ringkasan', 'Detail Order', 'Produk Teratas'], $spreadsheet->getSheetNames());

            $summary = $spreadsheet->getSheetByName('Ringkasan');
            $this->assertSame(170800.0, $summary->getCell('C6')->getValue(), 'Penjualan bersih salah');

            $detail = $spreadsheet->getSheetByName('Detail Order');
            $detailNumbers = collect($detail->toArray())
                ->map(fn ($row) => $row[0])
                ->filter(fn ($value) => $value !== null && str_starts_with((string) $value, 'ORD-'))
                ->values()
                ->all();

            $this->assertContains($todayA->order_number, $detailNumbers);
            $this->assertContains($todayB->order_number, $detailNumbers);
            $this->assertNotContains('ORD-999999', $detailNumbers);
        } finally {
            unlink($tmp);
        }
    }

    public function test_cashier_cannot_export_reports(): void
    {
        $this->actAsFresh($this->cashierUser());

        $this->get(route('admin.reports.excel'))->assertForbidden();
        $this->get(route('admin.reports.pdf'))->assertForbidden();
    }
}
