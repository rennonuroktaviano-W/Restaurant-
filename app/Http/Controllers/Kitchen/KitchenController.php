<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
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

        $newOrders = Order::with(['items.product', 'table', 'room', 'area'])
            ->where('has_kitchen_items', true)
            ->whereIn('order_status', [Order::STATUS_NEW, Order::STATUS_ACCEPTED])
            ->orderBy('ordered_at')
            ->get();

        $cooking = Order::with(['items.product', 'table', 'room', 'area'])
            ->where('has_kitchen_items', true)
            ->where('order_status', Order::STATUS_COOKING)
            ->orderBy('accepted_at')
            ->get();

        $ready = Order::with(['items.product', 'table', 'room', 'area'])
            ->where('has_kitchen_items', true)
            ->where('order_status', Order::STATUS_READY)
            ->orderBy('ready_at')
            ->get();

        return view('kitchen.board', compact('newOrders', 'cooking', 'ready'));
    }

    public function freshness(): JsonResponse
    {
        Gate::authorize('kitchen.view');

        $new = Order::query()
            ->where('has_kitchen_items', true)
            ->whereIn('order_status', [Order::STATUS_NEW, Order::STATUS_ACCEPTED])
            ->orderBy('ordered_at')
            ->pluck('order_status', 'id');

        $cooking = Order::query()
            ->where('has_kitchen_items', true)
            ->where('order_status', Order::STATUS_COOKING)
            ->orderBy('accepted_at')
            ->pluck('order_status', 'id');

        $ready = Order::query()
            ->where('has_kitchen_items', true)
            ->where('order_status', Order::STATUS_READY)
            ->orderBy('ready_at')
            ->pluck('order_status', 'id');

        $signature = md5(json_encode([$new, $cooking, $ready]));

        return response()->json(['signature' => $signature]);
    }
}
