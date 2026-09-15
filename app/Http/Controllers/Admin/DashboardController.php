<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('report.view');

        $range = $this->resolveRange($request);
        $period = $this->buildPeriod($range);
        $prevPeriod = $this->previousPeriod($period, $range);

        // Core KPIs: completed orders only (revenue basis) - use ordered_at for consistency with Reports
        $completedBase = fn ($start, $end) => Order::where('order_status', Order::STATUS_COMPLETED)
            ->whereBetween('ordered_at', [$start, $end]);

        // Single aggregate query for revenue + order count
        $agg = (clone $completedBase($period['start'], $period['end']))
            ->selectRaw('sum(grand_total) as revenue, count(*) as order_count, sum(discount_amount) as discount_amount')
            ->first();

        $grossSales = (float) ($agg->revenue ?? 0);
        $orderCount = (int) ($agg->order_count ?? 0);
        $discountAmount = (float) ($agg->discount_amount ?? 0);

        $aggPrev = (clone $completedBase($prevPeriod['start'], $prevPeriod['end']))
            ->selectRaw('sum(grand_total) as revenue, count(*) as order_count, sum(discount_amount) as discount_amount')
            ->first();

        $grossSalesPrev = (float) ($aggPrev->revenue ?? 0);
        $orderCountPrev = (int) ($aggPrev->order_count ?? 0);
        $discountAmountPrev = (float) ($aggPrev->discount_amount ?? 0);

        $avgOrderValue = $orderCount > 0 ? round($grossSales / $orderCount, 2) : 0;
        $avgOrderValuePrev = $orderCountPrev > 0 ? round($grossSalesPrev / $orderCountPrev, 2) : 0;

        $saleTrend = $this->trend($grossSales, $grossSalesPrev);
        $orderTrend = $this->trend($orderCount, $orderCountPrev);
        $avgTrend = $this->trend($avgOrderValue, $avgOrderValuePrev);
        $discountTrend = $this->trend($discountAmount, $discountAmountPrev);

        // Status distribution (based on ordered_at within period)
        $statusDistribution = Order::query()
            ->select('order_status', DB::raw('count(*) as total'))
            ->whereBetween('ordered_at', [$period['start'], $period['end']])
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        // Top products (completed orders in period) - use ordered_at for consistency with Reports
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_status', Order::STATUS_COMPLETED)
            ->whereBetween('orders.ordered_at', [$period['start'], $period['end']])
            ->select('order_items.product_name', DB::raw('sum(order_items.quantity) as qty'), DB::raw('sum(order_items.subtotal) as revenue'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Revenue by day for chart - use ordered_at for consistency with Reports
        $revenueByDay = DB::table('orders')
            ->where('order_status', Order::STATUS_COMPLETED)
            ->whereBetween('ordered_at', [$period['start'], $period['end']])
            ->select(
                DB::raw('DATE(ordered_at) as date'),
                DB::raw('sum(grand_total) as total'),
                DB::raw('count(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Payment method analytics (paid payments in period) - keep paid_at
        $paymentMix = Payment::query()
            ->join('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
            ->where('payments.status', Payment::STATUS_PAID)
            ->whereBetween('payments.paid_at', [$period['start'], $period['end']])
            ->select('payment_methods.name', DB::raw('count(*) as count'), DB::raw('sum(payments.amount) as total'))
            ->groupBy('payment_methods.name')
            ->orderByDesc('total')
            ->get();

        // Peak hours (completed orders in period) - use ordered_at for consistency
        // Database-agnostic: fetch timestamps then group by hour in PHP (avoids HOUR() / strftime() differences)
        $orderedAts = DB::table('orders')
            ->where('order_status', Order::STATUS_COMPLETED)
            ->whereBetween('ordered_at', [$period['start'], $period['end']])
            ->pluck('ordered_at');

        $peakHours = collect(range(0, 23))
            ->mapWithKeys(fn ($h) => [$h => 0]);

        foreach ($orderedAts as $ts) {
            $hour = (new Carbon($ts))->hour;
            $peakHours[$hour] = ($peakHours[$hour] ?? 0) + 1;
        }

        // Refund insights - keep refunds.created_at (cash basis)
        $refunds = Refund::query()
            ->join('orders', 'orders.id', '=', 'refunds.order_id')
            ->where('refunds.status', Refund::STATUS_SUCCEEDED)
            ->whereBetween('refunds.created_at', [$period['start'], $period['end']])
            ->select(
                DB::raw('count(*) as count'),
                DB::raw('sum(refunds.amount) as total')
            )
            ->first();

        $refundCount = (int) ($refunds->count ?? 0);
        $refundTotal = (float) ($refunds->total ?? 0);

        $refundsPrev = Refund::query()
            ->join('orders', 'orders.id', '=', 'refunds.order_id')
            ->where('refunds.status', Refund::STATUS_SUCCEEDED)
            ->whereBetween('refunds.created_at', [$prevPeriod['start'], $prevPeriod['end']])
            ->select(
                DB::raw('count(*) as count'),
                DB::raw('sum(refunds.amount) as total')
            )
            ->first();

        $refundCountPrev = (int) ($refundsPrev->count ?? 0);
        $refundTotalPrev = (float) ($refundsPrev->total ?? 0);

        $refundCountTrend = $this->trend($refundCount, $refundCountPrev);
        $refundTotalTrend = $this->trend($refundTotal, $refundTotalPrev);

        // Low stock
        $lowStock = Product::where('stock_type', 'limited')
            ->where('stock', '<=', 10)
            ->limit(10)
            ->get();

        // Orders for status summary (completed/cancelled/pending)
        $cancelledOrders = Order::where('order_status', Order::STATUS_CANCELLED)
            ->whereBetween('ordered_at', [$period['start'], $period['end']])
            ->count();
        $pendingOrders = Order::whereIn('order_status', [Order::STATUS_NEW, Order::STATUS_ACCEPTED, Order::STATUS_COOKING, Order::STATUS_READY])
            ->whereBetween('ordered_at', [$period['start'], $period['end']])
            ->count();

        // Recent orders (last 7 days, regardless of period filter for quick glance)
        $recentOrders = Order::with('table', 'room')
            ->where('ordered_at', '>=', now()->subDays(7))
            ->orderByDesc('ordered_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'range',
            'period',
            'grossSales',
            'grossSalesPrev',
            'orderCount',
            'orderCountPrev',
            'avgOrderValue',
            'avgOrderValuePrev',
            'saleTrend',
            'orderTrend',
            'avgTrend',
            'statusDistribution',
            'topProducts',
            'paymentMix',
            'revenueByDay',
            'peakHours',
            'discountAmount',
            'discountAmountPrev',
            'discountTrend',
            'refundCount',
            'refundTotal',
            'refundCountPrev',
            'refundTotalPrev',
            'refundCountTrend',
            'refundTotalTrend',
            'cancelledOrders',
            'pendingOrders',
            'lowStock',
            'recentOrders',
        ));
    }

    private function resolveRange(Request $request): string
    {
        $allowed = ['today', 'yesterday', '7d', '30d', 'month', 'custom'];
        $range = $request->query('range', 'month');

        if (! in_array($range, $allowed, true)) {
            $range = 'month';
        }

        if ($range === 'custom') {
            $from = $request->query('date_from');
            $to = $request->query('date_to');

            $fromValid = $from && Carbon::hasFormat($from, 'Y-m-d');
            $toValid = $to && Carbon::hasFormat($to, 'Y-m-d');

            if (! $fromValid || ! $toValid || $from > $to) {
                $range = 'month';
            }
        }

        return $range;
    }

    private function buildPeriod(string $range): array
    {
        $now = now();

        return match ($range) {
            'today' => ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()],
            'yesterday' => ['start' => $now->copy()->subDay()->startOfDay(), 'end' => $now->copy()->subDay()->endOfDay()],
            '7d' => ['start' => $now->copy()->subDays(6)->startOfDay(), 'end' => $now->copy()->endOfDay()],
            '30d' => ['start' => $now->copy()->subDays(29)->startOfDay(), 'end' => $now->copy()->endOfDay()],
            'month' => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
            default => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
        };
    }

    private function previousPeriod(array $period, string $range): array
    {
        // For "month" preset, use calendar month comparison (previous calendar month)
        if ($range === 'month') {
            $start = Carbon::parse($period['start']);

            return [
                'start' => $start->copy()->subMonthNoOverflow()->startOfMonth(),
                'end' => $start->copy()->subMonthNoOverflow()->endOfMonth(),
            ];
        }

        // For other presets, use same-duration rolling window
        $start = Carbon::parse($period['start']);
        $end = Carbon::parse($period['end']);
        $diff = $start->diffInDays($end) + 1;

        return [
            'start' => $start->copy()->subDays($diff)->startOfDay(),
            'end' => $end->copy()->subDays($diff)->endOfDay(),
        ];
    }

    private function trend(float|int $current, float|int $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? null : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
