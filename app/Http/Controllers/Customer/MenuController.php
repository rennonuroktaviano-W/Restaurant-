<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\LocationTokenService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(protected CartService $cart) {}

    public function home(Request $request): View
    {
        $this->handleLocationToken($request);

        $categories = Category::withCount('activeProducts')
            ->whereHas('products', fn ($q) => $q->visible())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $featured = Product::query()
            ->with('category')
            ->visible()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(3)
            ->get();

        $setting = fn (string $key) => (string) (Setting::where('key', $key)->value('value') ?? '');

        $activePromos = Discount::query()
            ->where('is_active', true)
            ->with('items')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('ends_at')
            ->get();

        return view('customer.home', [
            'siteName' => $setting('business.name') ?: config('app.name'),
            'categories' => $categories,
            'featured' => $featured,
            'activePromos' => $activePromos,
            'heroImages' => Product::query()->visible()->whereNotNull('image')->orderBy('sort_order')->limit(3)->pluck('image'),
            'areas' => Area::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'hasAddress' => $setting('business.address') !== '',
            'hasPhone' => $setting('business.phone') !== '',
            'address' => $setting('business.address'),
            'phone' => $setting('business.phone'),
            'cartCount' => $this->cart->count(),
            'cartQuantities' => $this->cart->all()->pluck('quantity', 'product_id')->toArray(),
            'drawerLines' => $this->cart->lines(),
            'drawerSubtotal' => $this->cart->subtotal(),
        ]);
    }

    public function index(Request $request): View
    {
        $this->handleLocationToken($request);

        $categories = Category::withCount('activeProducts')
            ->whereHas('products', fn ($q) => $q->visible())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('category')
            ->visible()
            ->when($request->q, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('customer.menu', $this->viewData($categories, $products));
    }

    public function category(Category $category): View
    {
        $categories = Category::withCount('activeProducts')
            ->whereHas('products', fn ($q) => $q->visible())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $products = $category->products()
            ->visible()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12);

        return view('customer.menu', $this->viewData($categories, $products));
    }

    private function handleLocationToken(Request $request): void
    {
        if (! $request->filled('location_token')) {
            return;
        }

        $location = app(LocationTokenService::class)->verify($request->string('location_token'));

        if ($location !== null) {
            session(['location.token' => $request->string('location_token')]);
        } else {
            session()->forget('location.token');
        }
    }

    private function viewData($categories, $products): array
    {
        return [
            'categories' => $categories,
            'products' => $products,
            'category' => request()->route('category'),
            'cartCount' => $this->cart->count(),
            'cartQuantities' => $this->cart->all()->pluck('quantity', 'product_id')->toArray(),
            'drawerLines' => $this->cart->lines(),
            'drawerSubtotal' => $this->cart->subtotal(),
        ];
    }
}
