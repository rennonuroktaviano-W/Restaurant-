<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        Gate::authorize('inventory.update');
    }

    public function index(Request $request): View
    {
        $warehouses = Warehouse::query()
            ->withCount('inventoryItems')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%"))
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('admin.warehouses.create');
    }

    public function store(WarehouseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['name']);
        }

        $warehouse = Warehouse::create($data);

        $this->audit->log('create', 'inventory', 'warehouse', $warehouse->id, [], $warehouse->toArray());

        return redirect()->route('admin.warehouses.index')->with('success', 'Gudang berhasil dibuat.');
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $old = $warehouse->toArray();

        $warehouse->update($request->validated());

        $this->audit->log('update', 'inventory', 'warehouse', $warehouse->id, $old, $warehouse->fresh()->toArray());

        return redirect()->route('admin.warehouses.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->inventoryItems()->exists()) {
            return back()->with('error', 'Gudang tidak dapat dihapus karena masih memiliki stok.');
        }

        $warehouse->delete();

        $this->audit->log('delete', 'inventory', 'warehouse', $warehouse->id, [], []);

        return redirect()->route('admin.warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);

        $count = Warehouse::withTrashed()->where('slug', $slug)->count();

        return $count > 0 ? $slug.'-'.($count + 1) : $slug;
    }
}
