<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\PaymentMethod;
use App\Models\Room;
use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
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
        $discountCode = session('cart.discount_code');

        return view('customer.cart', compact('lines', 'subtotal', 'pricing', 'areas', 'rooms', 'paymentMethods', 'discountCode'));
    }

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->cart->add((int) $data['product_id'], (int) ($data['quantity'] ?? 1), $data['notes'] ?? null);
        } catch (\RuntimeException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json($this->cartState());
        }

        return back()->with('success', 'Item ditambahkan ke keranjang.');
    }

    public function update(Request $request, int $productId): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->cart->update($productId, (int) $data['quantity'], $data['notes'] ?? null);

        if ($request->wantsJson()) {
            return response()->json($this->cartState());
        }

        return redirect()->route('cart.index');
    }

    public function applyDiscount(Request $request): RedirectResponse
    {
        $code = trim((string) $request->input('discount_code', ''));

        if ($code === '') {
            session()->forget('cart.discount_code');

            return redirect()->route('cart.index')->with('success', 'Kode promo dihapus.');
        }

        $lines = $this->cart->lines();
        $subtotal = $this->cart->subtotal();
        [$discount, $amount] = app(DiscountService::class)->applyCode($code, $lines, $subtotal);

        if (! $discount) {
            return back()->with('error', 'Kode promo tidak valid atau belum memenuhi minimal pembelian.');
        }

        session(['cart.discount_code' => $code]);

        return redirect()->route('cart.index')->with('success', 'Kode promo "'.$code.'" berhasil diterapkan!');
    }

    public function remove(Request $request, int $productId): RedirectResponse|JsonResponse
    {
        $this->cart->remove($productId);

        if ($request->wantsJson()) {
            return response()->json($this->cartState());
        }

        return redirect()->route('cart.index');
    }

    private function cartState(): array
    {
        $lines = $this->cart->lines();
        $subtotal = $this->cart->subtotal();

        return [
            'count' => $this->cart->count(),
            'subtotal' => $subtotal,
            'lines' => $lines,
            'pricing' => $this->pricing->calculate($lines, $subtotal, session('cart.discount_code')),
        ];
    }
}
