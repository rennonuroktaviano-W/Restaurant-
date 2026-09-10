<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
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

        // Keep aggregates OUTSIDE the paginated orders query to match Table 21.
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

        $paymentMix = $this->paymentMix($filters);

        $areas = Area::orderBy('name')->get();
        $cashiers = User::query()->role(['admin', 'manager', 'cashier'])->orderBy('name')->get();
        $paymentMethods = PaymentMethod::orderBy('name')->get();

        return view('admin.reports.index', compact(
            'orders',
            'grossSales',
            'netSales',
            'refundTotal',
            'orderCount',
            'cancelledCount',
            'avgOrderValue',
            'statusDistribution',
            'topProducts',
            'paymentMix',
            'areas',
            'cashiers',
            'paymentMethods',
        ) + ['filters' => $filters]);
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

    public function pdf(Request $request): Response
    {
        Gate::authorize('report.export');

        $filters = $this->filters($request);
        $orders = $this->baseQuery($filters)->orderByDesc('ordered_at')->get();
        $grossSales = (float) $orders->where('order_status', Order::STATUS_COMPLETED)->sum('grand_total');
        $refundTotal = $this->refundTotal($filters);
        $netSales = round($grossSales - $refundTotal, 2);

        $html = view('admin.reports.print', compact('orders', 'grossSales', 'netSales', 'refundTotal') + ['filters' => $filters])->render();

        return response($html)->header('Content-Type', 'text/html');
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
