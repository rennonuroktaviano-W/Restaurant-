<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiningTableRequest;
use App\Models\Area;
use App\Models\DiningTable;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        Gate::authorize('location.manage');
    }

    public function indexAll(Request $request): View
    {
        $tables = DiningTable::query()
            ->with('area')
            ->when($request->area, fn ($q, $a) => $q->where('area_id', $a))
            ->when($request->search, fn ($q, $s) => $q->where('table_number', 'like', "%{$s}%"))
            ->orderBy('area_id')
            ->orderBy('table_number')
            ->paginate(25)
            ->withQueryString();

        $areas = Area::orderBy('name')->get();

        return view('admin.dining-tables.index', compact('tables', 'areas'));
    }

    public function create(): View
    {
        $areas = Area::orderBy('name')->get();

        return view('admin.dining-tables.create', compact('areas'));
    }

    public function store(DiningTableRequest $request): RedirectResponse
    {
        $table = DiningTable::create($request->validated());

        $this->audit->log('create', 'location', 'dining_table', $table->id, [], $table->toArray());

        return redirect()->route('admin.dining-tables.index')->with('success', 'Meja berhasil dibuat.');
    }

    public function edit(DiningTable $table): View
    {
        $areas = Area::orderBy('name')->get();

        return view('admin.dining-tables.edit', compact('table', 'areas'));
    }

    public function update(DiningTableRequest $request, DiningTable $table): RedirectResponse
    {
        $old = $table->toArray();

        $table->update($request->validated());

        $this->audit->log('update', 'location', 'dining_table', $table->id, $old, $table->fresh()->toArray());

        return redirect()->route('admin.dining-tables.index')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(DiningTable $table): RedirectResponse
    {
        $table->delete();

        $this->audit->log('delete', 'location', 'dining_table', $table->id, [], []);

        return redirect()->route('admin.dining-tables.index')->with('success', 'Meja berhasil dihapus.');
    }
}
