<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AreaRequest;
use App\Models\Area;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        Gate::authorize('location.manage');
    }

    public function index(Request $request): View
    {
        $areas = Area::query()
            ->withCount(['diningTables', 'rooms'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.areas.index', compact('areas'));
    }

    public function create(): View
    {
        return view('admin.areas.create');
    }

    public function store(AreaRequest $request): RedirectResponse
    {
        $area = Area::create($request->validated() + ['slug' => $this->uniqueSlug($request->slug)]);

        $this->audit->log('create', 'location', 'area', $area->id, [], $area->toArray());

        return redirect()->route('admin.areas.index')->with('success', 'Area berhasil dibuat.');
    }

    public function edit(Area $area): View
    {
        return view('admin.areas.edit', compact('area'));
    }

    public function update(AreaRequest $request, Area $area): RedirectResponse
    {
        $old = $area->toArray();

        $area->update($request->validated() + ['slug' => $this->uniqueSlug($request->slug, $area->id)]);

        $this->audit->log('update', 'location', 'area', $area->id, $old, $area->fresh()->toArray());

        return redirect()->route('admin.areas.index')->with('success', 'Area berhasil diperbarui.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        if ($area->diningTables()->exists() || $area->rooms()->exists()) {
            return back()->with('error', 'Area masih memiliki meja/room. Nonaktifkan terlebih dahulu.');
        }

        $area->delete();

        $this->audit->log('delete', 'location', 'area', $area->id, [], []);

        return redirect()->route('admin.areas.index')->with('success', 'Area berhasil dihapus.');
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug);
        $candidate = $base;
        $i = 1;

        while (Area::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))->exists()) {
            $candidate = $base.'-'.$i++;
        }

        return $candidate;
    }
}
