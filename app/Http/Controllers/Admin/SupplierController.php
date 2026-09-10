<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        Gate::authorize('inventory.update');
    }

    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->withCount('movements')
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            }))
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('admin.suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::create($request->validated());

        $this->audit->log('create', 'inventory', 'supplier', $supplier->id, [], $supplier->toArray());

        return redirect()->route('admin.suppliers.index')->with('success', 'Pemasok berhasil dibuat.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $old = $supplier->toArray();

        $supplier->update($request->validated());

        $this->audit->log('update', 'inventory', 'supplier', $supplier->id, $old, $supplier->fresh()->toArray());

        return redirect()->route('admin.suppliers.index')->with('success', 'Pemasok berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        $this->audit->log('delete', 'inventory', 'supplier', $supplier->id, [], []);

        return redirect()->route('admin.suppliers.index')->with('success', 'Pemasok berhasil dihapus.');
    }
}
