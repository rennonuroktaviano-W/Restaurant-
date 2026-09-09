<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(protected CartService $cart) {}

    public function index(Request $request): View
    {
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

    private function viewData($categories, $products): array
    {
        return [
            'categories' => $categories,
            'products' => $products,
            'category' => request()->route('category'),
            'cartCount' => $this->cart->count(),
            'drawerLines' => $this->cart->lines(),
            'drawerSubtotal' => $this->cart->subtotal(),
        ];
    }
}
