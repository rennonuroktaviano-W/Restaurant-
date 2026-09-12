<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function show(Request $request): View
    {
        Gate::authorize('report.view');

        $cashier = $request->user();
        $shifts = [];

        // Simple shift summary: operations grouped by cashier (FR-CAS-008 P1 style).
        $methods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        $query = Order::query()
            ->where('created_by', $cashier->id)
            ->when($request->filled('date'), fn ($q, $d) => $q->whereDate('ordered_at', $d), fn ($q) => $q->whereDate('ordered_at', today()));

        $totalOrders = (clone $query)->count();
        $completed = (clone $query)->where('order_status', Order::STATUS_COMPLETED)->count();
        $cancelled = (clone $query)->where('order_status', Order::STATUS_CANCELLED)->count();

        $cashSales = (float) $query->where('order_status', Order::STATUS_COMPLETED)->sum('grand_total');

        $cashExpected = (float) Payment::query()
            ->where('created_by', $cashier->id)
            ->whereIn('status', [Payment::STATUS_PAID])
            ->whereDate('paid_at', $request->date ?? today())
            ->where('type', 'cash')
            ->sum('amount');

        // Per-method cash reconciliation, previously computed inside the Blade
        // view. Same clauses, prepared here so the view only renders values.
        $shiftDate = $request->date ?? today();
        $reconciliation = [];

        foreach ($methods->where('type', 'cash') as $method) {
            $base = Payment::query()
                ->where('created_by', $cashier->id)
                ->where('payment_method_id', $method->id)
                ->where('type', 'cash')
                ->whereDate('paid_at', $shiftDate);

            $received = (float) (clone $base)->where('status', Payment::STATUS_PAID)->sum('amount');
            $refunds = (float) (clone $base)->where('status', Payment::STATUS_REFUNDED)->sum('amount');

            $reconciliation[$method->id] = [
                'received' => $received,
                'refunds' => $refunds,
                'net' => max(0, $received - $refunds),
            ];
        }

        return view('cashier.shift', compact('cashier', 'methods', 'totalOrders', 'completed', 'cancelled', 'cashSales', 'cashExpected', 'reconciliation'));
    }
}
