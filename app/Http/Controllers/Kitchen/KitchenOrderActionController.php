<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AuditLogger;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class KitchenOrderActionController extends Controller
{
    public function __construct(protected OrderStatusService $status, protected AuditLogger $audit) {}

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('kitchen.update_status');

        if (! $order->has_kitchen_items) {
            return back()->with('error', 'Order ini tidak memiliki item dapur.');
        }

        $data = $request->validate([
            'action' => ['required', Rule::in(['start_cooking', 'mark_ready'])],
        ]);

        $target = $data['action'] === 'start_cooking' ? Order::STATUS_COOKING : Order::STATUS_READY;

        try {
            if ($order->order_status === Order::STATUS_NEW) {
                $this->status->transition($order, Order::STATUS_ACCEPTED, auth()->id());
            }

            if ($order->order_status !== $target) {
                $this->status->transition($order, $target, auth()->id());
            }
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log($data['action'], 'order', 'order', $order->id, [], ['order_status' => $order->fresh()->order_status]);

        return back()->with('success', 'Status order diperbarui.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('kitchen.update_status');

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $this->status->transition($order, Order::STATUS_CANCELLED, auth()->id(), $data['reason'], 'kitchen_cancel');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('cancel', 'order', 'order', $order->id, [], ['order_status' => 'cancelled']);

        return redirect()->route('kitchen.dashboard')->with('success', 'Order dibatalkan.');
    }
}
