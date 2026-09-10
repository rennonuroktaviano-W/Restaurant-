<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
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

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $warehouseId = $request->warehouse ?? null;

        $items = InventoryItem::query()
            ->with(['product.category', 'warehouse'])
            ->when($warehouseId, fn ($q, $w) => $q->where('warehouse_id', $w))
            ->when($request->search, fn ($q, $s) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")))
            ->orderBy('quantity')
            ->paginate(20)
            ->withQueryString();

        $lowStockThreshold = (int) $this->settings->get('inventory.low_stock_threshold', 10);

        $lowStockProducts = Product::query()
            ->with('category')
            ->where('stock_type', 'limited')
            ->where('is_active', true)
            ->where('stock', '<', $lowStockThreshold)
            ->orderBy('stock')
            ->get();

        $lowStockCount = $lowStockProducts->count();

        $warehouseLowStock = InventoryItem::query()
            ->with(['product', 'warehouse'])
            ->whereHas('product', fn ($p) => $p->where('stock_type', 'limited')->where('is_active', true))
            ->whereColumn('quantity', '<=', 'min_threshold')
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        return view('admin.inventory.index', compact('warehouses', 'warehouseId', 'items', 'lowStockProducts', 'lowStockCount', 'lowStockThreshold', 'warehouseLowStock'));
    }

    public function movements(Request $request): View
    {
        Gate::authorize('inventory.view');

        $movements = InventoryMovement::with(['product', 'actor', 'warehouse', 'toWarehouse', 'supplier'])
            ->when($request->product, fn ($q, $search) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->warehouse, fn ($q, $w) => $q->where(function ($q) use ($w) {
                $q->where('warehouse_id', $w)->orWhere('to_warehouse_id', $w);
            }))
            ->when($request->date, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.inventory.movements', compact('movements', 'warehouses'));
    }

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('inventory.update');

        $data = $request->validate([
            'new_stock' => ['required', 'integer', 'min:0'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStock = (int) $product->stock;

        try {
            $this->inventory->adjust($product, $data['new_stock'], $data['note'] ?? null, (string) auth()->id(), $data['warehouse_id'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->audit->log('adjust', 'inventory', 'product', $product->id, ['stock' => $oldStock], ['stock' => $product->stock, 'new_stock' => $data['new_stock']], auth()->id());

        return back()->with('success', 'Stok berhasil disesuaikan.');
    }

    public function stockInForm(Request $request): View
    {
        Gate::authorize('inventory.update');

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::query()
            ->where('stock_type', 'limited')
            ->where('is_active', true)
            ->when($request->product, fn ($q, $s) => $q->where(fn ($p) => $p->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%")))
            ->orderBy('name')
            ->get();

        return view('admin.inventory.stock-in', compact('warehouses', 'suppliers', 'products'));
    }

    public function stockIn(Request $request): RedirectResponse
    {
        Gate::authorize('inventory.update');

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $supplier = isset($data['supplier_id']) ? Supplier::find($data['supplier_id']) : null;

        $oldStock = (int) $product->stock;

        try {
            $this->inventory->stockIn(
                $product,
                $warehouse,
                $data['quantity'],
                $supplier,
                $data['note'] ?? null,
                isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
                (string) auth()->id(),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $this->audit->log('stock_in', 'inventory', 'product', $product->id, ['stock' => $oldStock], ['stock' => $product->stock, 'quantity' => $data['quantity']], auth()->id());

        return redirect()->route('admin.inventory.movements')->with('success', 'Stok masuk berhasil dicatat.');
    }

    public function stockOutForm(): View
    {
        Gate::authorize('inventory.update');

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::query()
            ->where('stock_type', 'limited')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.inventory.stock-out', compact('warehouses', 'products'));
    }

    public function stockOut(Request $request): RedirectResponse
    {
        Gate::authorize('inventory.update');

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $warehouse = Warehouse::findOrFail($data['warehouse_id']);

        $oldStock = (int) $product->stock;

        try {
            $this->inventory->stockOut($product, $warehouse, $data['quantity'], $data['note'] ?? null, (string) auth()->id());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $this->audit->log('stock_out', 'inventory', 'product', $product->id, ['stock' => $oldStock], ['stock' => $product->stock, 'quantity' => $data['quantity']], auth()->id());

        return redirect()->route('admin.inventory.movements')->with('success', 'Stok keluar berhasil dicatat.');
    }

    public function transferForm(): View
    {
        Gate::authorize('inventory.update');

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::query()
            ->where('stock_type', 'limited')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.inventory.transfer', compact('warehouses', 'products'));
    }

    public function transfer(Request $request): RedirectResponse
    {
        Gate::authorize('inventory.update');

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $from = Warehouse::findOrFail($data['from_warehouse_id']);
        $to = Warehouse::findOrFail($data['to_warehouse_id']);

        try {
            $this->inventory->transfer($product, $from, $to, $data['quantity'], $data['note'] ?? null, (string) auth()->id());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $this->audit->log('transfer', 'inventory', 'product', $product->id, [], ['quantity' => $data['quantity'], 'from' => $from->name, 'to' => $to->name], auth()->id());

        return redirect()->route('admin.inventory.movements')->with('success', 'Transfer stok berhasil.');
    }
}
