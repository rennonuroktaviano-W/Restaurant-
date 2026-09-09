<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function __construct(protected SettingsService $settings) {}

    public function dashboard(): View
    {
        Gate::authorize('kitchen.view');

        if (! (bool) $this->settings->get('feature.kds_enabled', true)) {
            return view('kitchen.disabled');
        }

        $newOrders = Order::with(['items', 'table', 'room', 'area'])
            ->where('has_kitchen_items', true)
            ->whereIn('order_status', [Order::STATUS_NEW, Order::STATUS_ACCEPTED])
            ->orderBy('ordered_at')
            ->get();

        $cooking = Order::with(['items', 'table', 'room', 'area'])
            ->where('has_kitchen_items', true)
            ->where('order_status', Order::STATUS_COOKING)
            ->orderBy('accepted_at')
            ->get();

        $ready = Order::with(['items', 'table', 'room', 'area'])
            ->where('has_kitchen_items', true)
            ->where('order_status', Order::STATUS_READY)
            ->orderBy('ready_at')
            ->get();

        return view('kitchen.board', compact('newOrders', 'cooking', 'ready'));
    }
}
