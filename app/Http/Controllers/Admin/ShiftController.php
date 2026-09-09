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

        return view('cashier.shift', compact('cashier', 'methods', 'totalOrders', 'completed', 'cancelled', 'cashSales', 'cashExpected'));
    }
}
