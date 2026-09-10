<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        Gate::authorize('catalog.manage');
    }

    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->with(['products' => fn ($q) => $q->with('category')->orderBy('sort_order')->orderBy('name')])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['slug']);

        $category = DB::transaction(function () use ($request, $data) {
            $category = Category::create($data);

            if ($request->hasFile('image')) {
                $category->update(['image' => $request->file('image')->store('categories', 'public')]);
            }

            return $category;
        });

        $this->audit->log('create', 'catalog', 'category', $category->id, [], $category->toArray());

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $old = $category->toArray();

        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['slug'], $category->id);

        DB::transaction(function () use ($request, $category, $data) {
            $category->update($data);

            if ($request->hasFile('image')) {
                $category->update(['image' => $request->file('image')->store('categories', 'public')]);
            }
        });

        $this->audit->log('update', 'catalog', 'category', $category->id, $old, $category->fresh()->toArray());

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori masih memiliki produk. Pindahkan atau nonaktifkan terlebih dahulu.');
        }

        $category->delete();

        $this->audit->log('delete', 'catalog', 'category', $category->id, [], []);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug);
        $candidate = $base;
        $i = 1;

        while (Category::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))->exists()) {
            $candidate = $base.'-'.$i++;
        }

        return $candidate;
    }
}
