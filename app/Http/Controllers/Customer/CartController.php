<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\PaymentMethod;
use App\Models\Room;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(protected CartService $cart, protected PricingService $pricing) {}

    public function index(): View
    {
        $lines = $this->cart->lines();
        $subtotal = $this->cart->subtotal();
        $pricing = $this->pricing->calculate($lines, $subtotal, session('cart.discount_code'));

        session(['checkout.key' => session('checkout.key') ?? Str::uuid()->toString()]);

        $areas = Area::with(['diningTables' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $rooms = Room::where('is_active', true)
            ->with('area')
            ->orderBy('name')
            ->get();

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        return view('customer.cart', compact('lines', 'subtotal', 'pricing', 'areas', 'rooms', 'paymentMethods'));
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->cart->add((int) $data['product_id'], (int) ($data['quantity'] ?? 1), $data['notes'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Item ditambahkan ke keranjang.');
    }

    public function update(Request $request, int $productId): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->cart->update($productId, (int) $data['quantity'], $data['notes'] ?? null);

        return redirect()->route('cart.index');
    }

    public function remove(Request $request, int $productId): RedirectResponse
    {
        $this->cart->remove($productId);

        return redirect()->route('cart.index');
    }
}
