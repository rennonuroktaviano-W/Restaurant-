<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('report.view');

        $filters = $this->filters($request);
        $base = $this->baseQuery($filters);

        $orders = (clone $base)
            ->with(['items', 'table', 'room', 'area'])
            ->orderByDesc('ordered_at')
            ->paginate(25)
            ->withQueryString();

        $aggregates = $this->aggregates($filters);
        $paymentMix = $this->paymentMix($filters);

        $areas = Area::orderBy('name')->get();
        $cashiers = User::query()->role(['admin', 'manager', 'cashier'])->orderBy('name')->get();
        $paymentMethods = PaymentMethod::orderBy('name')->get();

        return view('admin.reports.index', array_merge($aggregates, [
            'orders' => $orders,
            'paymentMix' => $paymentMix,
            'areas' => $areas,
            'cashiers' => $cashiers,
            'paymentMethods' => $paymentMethods,
            'filters' => $filters,
        ]));
    }

    public function export(Request $request): StreamedResponse
    {
        Gate::authorize('report.export');

        $filters = $this->filters($request);
        $orders = $this->baseQuery($filters)->with(['items'])->orderByDesc('ordered_at')->get();

        $callback = function () use ($orders) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Order Number', 'Tanggal', 'Tipe', 'Lokasi', 'Status', 'Payment Status',
                'Subtotal', 'Diskon', 'Pajak', 'Service Charge', 'Total', 'Items', 'Kasir',
            ]);

            foreach ($orders as $order) {
                fputcsv($out, [
                    $order->order_number,
                    $order->ordered_at?->toDateTimeString(),
                    $order->order_type,
                    $order->locationLabel(),
                    $order->order_status,
                    $order->payment_status,
                    number_format((float) $order->subtotal, 2, ',', '.'),
                    number_format((float) $order->discount_amount, 2, ',', '.'),
                    number_format((float) $order->tax_amount, 2, ',', '.'),
                    number_format((float) $order->service_charge_amount, 2, ',', '.'),
                    number_format((float) $order->grand_total, 2, ',', '.'),
                    $order->items->sum('quantity'),
                    $order->creator?->name ?? '-',
                ]);
            }

            fclose($out);
        };

        $filename = 'laporan-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function excel(Request $request): StreamedResponse
    {
        Gate::authorize('report.export');

        $filters = $this->filters($request);
        $orders = $this->baseQuery($filters)->with(['items'])->orderByDesc('ordered_at')->get();
        $aggregates = $this->aggregates($filters);
        $paymentMix = $this->paymentMix($filters);

        $totalOrders = $orders->sum('grand_total');

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator(auth()->user()?->name ?? config('app.name'))
            ->setTitle('Laporan Penjualan '.now()->format('d/m/Y'));

        $this->buildSummarySheet($spreadsheet->getActiveSheet(), $aggregates, $paymentMix, $filters);
        $this->buildOrdersSheet($spreadsheet->createSheet(), $orders);
        $this->buildTopProductsSheet($spreadsheet->createSheet(), $aggregates['topProducts']);
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'laporan-penjualan-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function pdf(Request $request): Response
    {
        Gate::authorize('report.export');

        $filters = $this->filters($request);
        $orders = $this->baseQuery($filters)->with(['items'])->orderByDesc('ordered_at')->get();
        $aggregates = $this->aggregates($filters);
        $paymentMix = $this->paymentMix($filters);

        $pdf = Pdf::loadView('admin.reports.print', array_merge($aggregates, [
            'orders' => $orders,
            'paymentMix' => $paymentMix,
            'filters' => $filters,
            'appliedFilters' => $this->appliedFilters($filters),
            'periodLabel' => $this->periodLabel($filters),
        ]))->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-penjualan-'.now()->format('Ymd-His').'.pdf');
    }

    private function buildSummarySheet($sheet, array $aggregates, $paymentMix, array $filters): void
    {
        $sheet->setTitle('Ringkasan');

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'LAPORAN PENJUALAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Periode: '.$this->periodLabel($filters));

        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'Dicetak: '.now()->format('d/m/Y H:i').' — '.auth()->user()?->name ?? '-');
        $sheet->getStyle('A2:A3')->getFont()->setSize(10);

        $labels = ['Penjualan Kotor', 'Refund', 'Penjualan Bersih', 'Order Selesai', 'Dibatalkan', 'Rata-rata Order'];
        $values = [$aggregates['grossSales'], $aggregates['refundTotal'], $aggregates['netSales'], $aggregates['orderCount'], $aggregates['cancelledCount'], $aggregates['avgOrderValue']];

        foreach ($labels as $i => $label) {
            $column = chr(65 + $i);
            $sheet->setCellValue("{$column}5", $label);
            $sheet->getStyle("{$column}5")->getFont()->setBold(true);
            $sheet->getStyle("{$column}5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE7EAEE');
            $sheet->getStyle("{$column}5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $cell = "{$column}6";
            $sheet->setCellValue($cell, $values[$i]);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        $sheet->setCellValue('A8', 'Metode Pembayaran Teratas: '.($paymentMix->first()?->name ?? '-'));
        $sheet->getStyle('A8')->getFont()->setSize(10);

        $appliedFilters = $this->appliedFilters($filters);

        if ($appliedFilters !== []) {
            $row = 10;
            $sheet->setCellValue("A{$row}", 'FILTER YANG DITERAPKAN');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);

            foreach ($appliedFilters as $label => $value) {
                $row++;
                $sheet->setCellValue("A{$row}", $label);
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->mergeCells("B{$row}:F{$row}");
                $sheet->setCellValue("B{$row}", $value);
            }
        }

        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function buildOrdersSheet($sheet, $orders): void
    {
        $sheet->setTitle('Detail Order');

        $headers = [
            'Order Number', 'Tanggal', 'Tipe', 'Lokasi', 'Status', 'Payment Status',
            'Subtotal', 'Diskon', 'Pajak', 'Service Charge', 'Total', 'Items', 'Kasir',
        ];

        foreach ($headers as $i => $header) {
            $cell = chr(65 + $i).'1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F2937');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $row = 2;
        foreach ($orders as $order) {
            $sheet->setCellValue("A{$row}", $order->order_number);
            $sheet->setCellValue("B{$row}", $order->ordered_at?->format('d/m/Y H:i'));
            $sheet->setCellValue("C{$row}", ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'][$order->order_type] ?? $order->order_type);
            $sheet->setCellValue("D{$row}", $order->area?->name.' / '.$order->locationLabel());
            $sheet->setCellValue("E{$row}", Order::$flowLabels[$order->order_status] ?? $order->order_status);
            $sheet->setCellValue("F{$row}", ['paid' => 'Lunas', 'pending' => 'Belum', 'failed' => 'Gagal', 'expired' => 'Kadaluarsa'][$order->payment_status] ?? $order->payment_status);
            $sheet->setCellValue("G{$row}", (float) $order->subtotal);
            $sheet->setCellValue("H{$row}", (float) $order->discount_amount);
            $sheet->setCellValue("I{$row}", (float) $order->tax_amount);
            $sheet->setCellValue("J{$row}", (float) $order->service_charge_amount);
            $sheet->setCellValue("K{$row}", (float) $order->grand_total);
            $sheet->setCellValue("L{$row}", $order->items->sum('quantity'));
            $sheet->setCellValue("M{$row}", $order->creator?->name ?? '-');

            foreach (['G', 'H', 'I', 'J', 'K'] as $column) {
                $sheet->getStyle("{$column}{$row}")->getNumberFormat()->setFormatCode('#,##0');
            }

            $totalRow = 'K'.$row;
            $sheet->getStyle($totalRow)->getFont()->setBold(true);

            $row++;
        }

        $lastRow = $row - 1;

        if ($lastRow < 2) {
            $lastRow = 2;
            $sheet->setCellValue('A2', 'Tidak ada data pada filter ini.');
            $sheet->mergeCells('A2:M2');
        } else {
            $sheet->setCellValue("A{$row}", 'TOTAL');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE7EAEE');

            $sheet->setCellValue("K{$row}", (float) $orders->sum('grand_total'));
            $sheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->setCellValue("L{$row}", $orders->sum(fn ($order) => $order->items->sum('quantity')));

            $sheet->getStyle("A{$row}:M{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:M{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE7EAEE');
            $sheet->getStyle("A{$row}:M{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $sheet->setAutoFilter("A1:M{$lastRow}");
        $sheet->freezePane('A2');

        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function buildTopProductsSheet($sheet, $topProducts): void
    {
        $sheet->setTitle('Produk Teratas');

        foreach (['Produk', 'Qty', 'Pendapatan'] as $i => $header) {
            $cell = chr(65 + $i).'1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1F2937');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $row = 2;
        foreach ($topProducts as $product) {
            $sheet->setCellValue("A{$row}", $product->product_name);
            $sheet->setCellValue("B{$row}", (int) $product->qty);
            $sheet->setCellValue("C{$row}", (int) $product->revenue);
            $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->setAutoFilter('A1:C'.max(1, $row - 1));

        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function baseQuery(array $filters)
    {
        return Order::query()
            ->when($filters['search'], fn ($q, $s) => $q->where(function ($query) use ($s) {
                $query->where('order_number', 'like', "%{$s}%")
                    ->orWhere('customer_name', 'like', "%{$s}%")
                    ->orWhere('customer_phone', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%");
            }))
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('ordered_at', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('ordered_at', '<=', $v))
            ->when($filters['area_id'], fn ($q, $v) => $q->where('area_id', $v))
            ->when($filters['order_type'], fn ($q, $v) => $q->where('order_type', $v))
            ->when($filters['status'], fn ($q, $v) => $q->where('order_status', $v))
            ->when($filters['cashier_id'], fn ($q, $v) => $q->where('created_by', $v))
            ->when($filters['payment_method_id'], fn ($q, $v) => $q->whereHas('payments', fn ($p) => $p->where('payment_method_id', $v)->where('status', 'paid')));
    }

    private function aggregates(array $filters): array
    {
        $base = $this->baseQuery($filters);

        $grossSales = (float) (clone $base)->where('order_status', Order::STATUS_COMPLETED)->sum('grand_total');
        $refundTotal = $this->refundTotal($filters);
        $netSales = round($grossSales - $refundTotal, 2);

        $orderCount = (clone $base)->where('order_status', Order::STATUS_COMPLETED)->count();
        $cancelledCount = (clone $base)->where('order_status', Order::STATUS_CANCELLED)->count();

        $avgOrderValue = $orderCount > 0 ? round($grossSales / $orderCount, 2) : 0;

        $statusDistribution = (clone $base)
            ->select('order_status', DB::raw('count(*) as total'))
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $topProducts = (clone $base)
            ->where('orders.order_status', Order::STATUS_COMPLETED)
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->select('order_items.product_name', DB::raw('sum(order_items.quantity) as qty'), DB::raw('sum(order_items.subtotal) as revenue'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        return compact('grossSales', 'refundTotal', 'netSales', 'orderCount', 'cancelledCount', 'avgOrderValue', 'statusDistribution', 'topProducts');
    }

    private function refundTotal(array $filters): float
    {
        return (float) Refund::query()
            ->join('orders', 'orders.id', '=', 'refunds.order_id')
            ->where('refunds.status', Refund::STATUS_SUCCEEDED)
            ->when($filters['search'], fn ($q, $s) => $q->where(function ($query) use ($s) {
                $query->where('orders.order_number', 'like', "%{$s}%")
                    ->orWhere('orders.customer_name', 'like', "%{$s}%")
                    ->orWhere('orders.customer_phone', 'like', "%{$s}%")
                    ->orWhere('orders.notes', 'like', "%{$s}%");
            }))
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('refunds.created_at', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('refunds.created_at', '<=', $v))
            ->when($filters['area_id'], fn ($q, $v) => $q->where('orders.area_id', $v))
            ->when($filters['order_type'], fn ($q, $v) => $q->where('orders.order_type', $v))
            ->when($filters['status'], fn ($q, $v) => $q->where('orders.order_status', $v))
            ->when($filters['cashier_id'], fn ($q, $v) => $q->where('orders.created_by', $v))
            ->sum('refunds.amount');
    }

    private function paymentMix(array $filters)
    {
        return Payment::query()
            ->join('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('payments.paid_at', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('payments.paid_at', '<=', $v))
            ->where('payments.status', Payment::STATUS_PAID)
            ->select('payment_methods.name', DB::raw('count(*) as count'), DB::raw('sum(payments.amount) as total'))
            ->groupBy('payment_methods.name')
            ->orderByDesc('total')
            ->get();
    }

    private function appliedFilters(array $filters): array
    {
        $applied = [];

        if ($filters['search']) {
            $applied['Pencarian'] = $filters['search'];
        }

        if ($filters['date_from']) {
            $applied['Dari Tanggal'] = $filters['date_from'];
        }

        if ($filters['date_to']) {
            $applied['Sampai Tanggal'] = $filters['date_to'];
        }

        if ($filters['area_id']) {
            $applied['Area'] = Area::find($filters['area_id'])?->name ?? $filters['area_id'];
        }

        if ($filters['order_type']) {
            $applied['Tipe Order'] = ['dine_in' => 'Dine In', 'take_away' => 'Take Away', 'room_service' => 'Room Service'][$filters['order_type']] ?? $filters['order_type'];
        }

        if ($filters['status']) {
            $applied['Status'] = Order::$flowLabels[$filters['status']] ?? $filters['status'];
        }

        if ($filters['cashier_id']) {
            $applied['Kasir'] = User::find($filters['cashier_id'])?->name ?? $filters['cashier_id'];
        }

        if ($filters['payment_method_id']) {
            $applied['Metode Bayar'] = PaymentMethod::find($filters['payment_method_id'])?->name ?? $filters['payment_method_id'];
        }

        return $applied;
    }

    private function periodLabel(array $filters): string
    {
        $from = $filters['date_from'] ? date('d/m/Y', strtotime($filters['date_from'])) : 'Awal';
        $to = $filters['date_to'] ? date('d/m/Y', strtotime($filters['date_to'])) : 'Sekarang';

        return $from.' s/d '.$to;
    }

    private function filters(Request $request): array
    {
        return [
            'search' => $request->search,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'area_id' => $request->area_id,
            'order_type' => $request->order_type,
            'status' => $request->status,
            'cashier_id' => $request->cashier_id,
            'payment_method_id' => $request->payment_method_id,
        ];
    }
}
