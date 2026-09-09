<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    /**
     * Atomically decrement stock for each order item (FR-CAT-006, BR-010).
     *
     * @param  Collection<int, Product>  $products  indexed by id
     */
    public function reserve(Order $order, Collection $products): void
    {
        foreach ($order->items as $item) {
            $product = $products->get($item->product_id);

            if (! $product || ! $product->isLimitedStock()) {
                continue;
            }

            $this->decrement($product, $item->quantity, OrderItem::class, $item->id);
        }
    }

    /**
     * Reverse a prior OUT for a cancelled order (BR-010).
     */
    public function reverse(Order $order): void
    {
        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::withTrashed()->find($item->product_id);

            if (! $product || ! $product->isLimitedStock()) {
                continue;
            }

            $this->increment($product, $item->quantity, Order::class, $order->id);
        }
    }

    /**
     * Decrement using a row-locked atomic update so stock never goes negative (FR-CAT-006).
     */
    public function decrement(Product $product, int $quantity, string $refType = 'NONE', ?int $refId = null, ?string $actorId = null): void
    {
        $product->refresh();

        $before = (int) $product->stock;

        if ($before < $quantity) {
            throw new RuntimeException('Stok produk tidak mencukupi untuk '.$product->name);
        }

        $affected = DB::table('products')
            ->where('id', $product->id)
            ->where('stock', '>=', $quantity)
            ->decrement('stock', $quantity);

        if ($affected === 0) {
            throw new RuntimeException('Stok produk tidak mencukupi untuk '.$product->name);
        }

        $after = $before - $quantity;

        $this->movement($product, InventoryMovement::TYPE_OUT, $quantity, $before, $after, $refType, $refId, $actorId);
    }

    public function increment(Product $product, int $quantity, string $refType = 'NONE', ?int $refId = null, ?string $actorId = null): void
    {
        $product->refresh();

        $before = (int) $product->stock;
        $after = $before + $quantity;

        $product->increment('stock', $quantity);

        $this->movement($product, InventoryMovement::TYPE_REVERSAL, $quantity, $before, $after, $refType, $refId, $actorId);
    }

    public function adjust(Product $product, int $newStock, ?string $note = null, ?string $actorId = null): void
    {
        $product->refresh();

        if ($newStock < 0) {
            throw new RuntimeException('Stok tidak boleh negatif');
        }

        $before = (int) $product->stock;
        $delta = $newStock - $before;

        $product->update(['stock' => $newStock]);

        $this->movement($product, InventoryMovement::TYPE_ADJUSTMENT, $delta, $before, $newStock, 'ADMIN', null, $actorId, $note);
    }

    private function movement(
        Product $product,
        string $type,
        int $quantity,
        int $before,
        int $after,
        string $refType = 'NONE',
        ?int $refId = null,
        ?string $actorId = null,
        ?string $note = null,
    ): void {
        InventoryMovement::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => $quantity,
            'before' => $before,
            'after' => $after,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'actor_id' => $actorId,
            'note' => $note,
            'created_at' => now(),
        ]);
    }
}
