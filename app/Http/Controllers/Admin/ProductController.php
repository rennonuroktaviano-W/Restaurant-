<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\AuditLogger;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(protected InventoryService $inventory, protected AuditLogger $audit)
    {
        Gate::authorize('catalog.manage');
    }

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->when($request->search, fn ($q, $s) => $q->where(function ($query) use ($s) {
                $query->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%");
            }))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(Request $request): View
    {
        $categories = Category::orderBy('name')->get();
        $selectedCategory = $request->filled('category_id') ? (int) $request->category_id : null;

        return view('admin.products.create', compact('categories', 'selectedCategory'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['slug']);

        $product = DB::transaction(function () use ($request, $data) {
            $product = Product::create($data);

            if ($request->hasFile('image')) {
                $product->update(['image' => $request->file('image')->store('products', 'public')]);
            }

            return $product;
        });

        $this->audit->log('create', 'catalog', 'product', $product->id, [], $product->fresh()->toArray());

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $old = $product->toArray();
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['slug'], $product->id);

        DB::transaction(function () use ($request, $product, $data) {
            $product->update($data);

            if ($request->hasFile('image')) {
                $product->update(['image' => $request->file('image')->store('products', 'public')]);
            }
        });

        $this->audit->log('update', 'catalog', 'product', $product->id, $old, $product->fresh()->toArray());

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        $this->audit->log('delete', 'catalog', 'product', $product->id, [], []);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug);
        $candidate = $base;
        $i = 1;

        while (Product::withTrashed()->where('slug', $candidate)->when($ignoreId, fn ($q, $id) => $q->where('id', '!=', $id))->exists()) {
            $candidate = $base.'-'.$i++;
        }

        return $candidate;
    }
}
