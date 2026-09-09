<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Services\AuditLogger;
use App\Services\InventoryService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventory,
        protected AuditLogger $audit,
        protected SettingsService $settings,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('inventory.view');

        $products = Product::query()
            ->with('category')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"))
            ->orderBy('stock_type')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $lowStockThreshold = (int) $this->settings->get('inventory.low_stock_threshold', 10);

        $lowStockProducts = Product::query()
            ->where('stock_type', 'limited')
            ->where('is_active', true)
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock')
            ->get();

        $lowStockCount = $lowStockProducts->count();

        return view('admin.inventory.index', compact('products', 'lowStockProducts', 'lowStockCount', 'lowStockThreshold'));
    }

    public function movements(Request $request): View
    {
        Gate::authorize('inventory.view');

        $movements = InventoryMovement::with(['product', 'actor'])
            ->when($request->product, fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->date, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.inventory.movements', compact('movements'));
    }

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('inventory.update');

        $data = $request->validate([
            'new_stock' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->inventory->adjust($product, $data['new_stock'], $data['note'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('adjust', 'inventory', 'product', $product->id, [], ['stock' => $product->stock, 'new_stock' => $data['new_stock']]);

        return back()->with('success', 'Stok berhasil disesuaikan.');
    }
}
