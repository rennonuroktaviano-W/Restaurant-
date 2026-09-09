<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use RuntimeException;

class CartService
{
    protected const SESSION_KEY = 'customer_cart';

    public function all(): Collection
    {
        return collect(session(self::SESSION_KEY, []));
    }

    public function add(int $productId, int $quantity = 1, ?string $notes = null): void
    {
        $product = Product::find($productId);

        if (! $product || ! $product->is_active || ! $product->is_available) {
            throw new RuntimeException('Produk sedang tidak tersedia');
        }

        $quantity = max(1, $quantity);

        $cart = $this->all();

        $cart = $cart->map(function ($line) use ($productId, $quantity) {
            if (($line['product_id'] ?? null) === $productId) {
                $line['quantity'] += $quantity;

                return $line;
            }

            return $line;
        });

        if (! $cart->contains(fn ($line) => ($line['product_id'] ?? null) === $productId)) {
            $cart->push([
                'product_id' => $productId,
                'quantity' => $quantity,
                'notes' => $notes,
            ]);
        }

        $this->persist($cart);
    }

    public function update(int $productId, int $quantity, ?string $notes = null): void
    {
        if ($quantity <= 0) {
            $this->remove($productId);

            return;
        }

        $cart = $this->all()
            ->map(function ($line) use ($productId, $quantity, $notes) {
                if (($line['product_id'] ?? null) === $productId) {
                    $line['quantity'] = $quantity;
                    $line['notes'] = $notes;

                    return $line;
                }

                return $line;
            });

        $this->persist($cart);
    }

    public function remove(int $productId): void
    {
        $this->persist($this->all()->reject(fn ($line) => ($line['product_id'] ?? null) === $productId)->values());
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return $this->all()->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->all()->isEmpty();
    }

    /**
     * Enrich cart lines with live product data (server authoritative).
     */
    public function lines(): Collection
    {
        return $this->all()->map(function ($line) {
            $product = Product::with('category')->find($line['product_id']);

            return [
                'product_id' => $line['product_id'],
                'category_id' => $product?->category_id,
                'product_name' => $product?->name ?? '(produk tidak tersedia)',
                'product_slug' => $product?->slug,
                'image' => $product?->image,
                'price' => (float) ($product?->sale_price ?? 0),
                'quantity' => (int) $line['quantity'],
                'notes' => $line['notes'] ?? null,
                'available' => $product && $product->is_active && $product->is_available,
                'limited' => $product?->isLimitedStock() ?? false,
                'stock' => $product?->stock ?? 0,
            ];
        })->values();
    }

    public function subtotal(): float
    {
        return round($this->lines()->sum(fn ($l) => $l['price'] * $l['quantity']), 2);
    }

    public function toCheckoutPayload(): array
    {
        return $this->all()
            ->filter(fn ($line) => isset($line['product_id']))
            ->map(fn ($line) => [
                'product_id' => (int) $line['product_id'],
                'quantity' => (int) $line['quantity'],
                'notes' => $line['notes'] ?? null,
            ])
            ->values()
            ->all();
    }

    private function persist(Collection $cart): void
    {
        session([self::SESSION_KEY => $cart->values()->all()]);
    }
}
