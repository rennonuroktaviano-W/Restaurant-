<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
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

        $this->decrementDefaultWarehouse($product, $quantity);
    }

    public function increment(Product $product, int $quantity, string $refType = 'NONE', ?int $refId = null, ?string $actorId = null): void
    {
        $product->refresh();

        $before = (int) $product->stock;
        $after = $before + $quantity;

        $product->increment('stock', $quantity);

        $this->movement($product, InventoryMovement::TYPE_REVERSAL, $quantity, $before, $after, $refType, $refId, $actorId);

        $this->incrementDefaultWarehouse($product, $quantity);
    }

    public function adjust(Product $product, int $newStock, ?string $note = null, ?string $actorId = null, ?int $warehouseId = null): void
    {
        $product->refresh();

        if ($newStock < 0) {
            throw new RuntimeException('Stok tidak boleh negatif');
        }

        $before = (int) $product->stock;
        $delta = $newStock - $before;

        $warehouse = Warehouse::find($warehouseId) ?? $this->defaultWarehouse();
        $item = $this->ensureInventoryItem($warehouse, $product);

        DB::transaction(function () use ($item, $delta) {
            if ($item->quantity + $delta < 0) {
                throw new RuntimeException('Stok gudang tidak mencukupi');
            }
            $item->increment('quantity', $delta);
        });

        $product->update(['stock' => $newStock]);

        $this->movement($product, InventoryMovement::TYPE_ADJUSTMENT, $delta, $before, $newStock, 'ADMIN', null, $actorId, $note, $warehouse);
    }

    /**
     * Record incoming stock from a supplier into a specific warehouse.
     */
    public function stockIn(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        ?Supplier $supplier = null,
        ?string $note = null,
        ?float $unitCost = null,
        ?string $actorId = null,
    ): void {
        $product->refresh();

        $before = (int) $product->stock;
        $item = $this->ensureInventoryItem($warehouse, $product);
        $itemAfter = $item->quantity + $quantity;

        DB::transaction(function () use ($item, $itemAfter) {
            $item->update(['quantity' => $itemAfter]);
        });

        $product->increment('stock', $quantity);

        $this->movement(
            $product,
            InventoryMovement::TYPE_IN,
            $quantity,
            $before,
            $before + $quantity,
            Supplier::class,
            $supplier?->id,
            $actorId,
            $note,
            $warehouse,
            $supplier,
            $unitCost,
        );
    }

    /**
     * Remove stock from a specific warehouse (e.g. damaged, expired, waste).
     */
    public function stockOut(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        ?string $note = null,
        ?string $actorId = null,
    ): void {
        $product->refresh();

        $item = $this->getInventoryItem($warehouse, $product);

        if (! $item || $item->quantity < $quantity) {
            throw new RuntimeException('Stok gudang tidak mencukupi untuk '.$product->name);
        }

        $before = (int) $product->stock;

        DB::transaction(function () use ($item, $product, $quantity) {
            $affected = InventoryItem::query()
                ->whereKey($item->id)
                ->where('quantity', '>=', $quantity)
                ->decrement('quantity', $quantity);

            if ($affected === 0) {
                throw new RuntimeException('Stok gudang tidak mencukupi untuk '.$product->name);
            }

            $affectedProduct = DB::table('products')
                ->where('id', $product->id)
                ->where('stock', '>=', $quantity)
                ->decrement('stock', $quantity);

            if ($affectedProduct === 0) {
                throw new RuntimeException('Stok produk tidak mencukupi untuk '.$product->name);
            }
        });

        $this->movement(
            $product,
            InventoryMovement::TYPE_OUT,
            $quantity,
            $before,
            $before - $quantity,
            'MANUAL',
            null,
            $actorId,
            $note,
            $warehouse,
        );
    }

    /**
     * Move stock between warehouses. Product total stock is unchanged.
     */
    public function transfer(
        Product $product,
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        int $quantity,
        ?string $note = null,
        ?string $actorId = null,
    ): void {
        if ($fromWarehouse->id === $toWarehouse->id) {
            throw new RuntimeException('Gudang asal dan tujuan tidak boleh sama');
        }

        $from = $this->getInventoryItem($fromWarehouse, $product);

        if (! $from || $from->quantity < $quantity) {
            throw new RuntimeException('Stok gudang asal tidak mencukupi untuk '.$product->name);
        }

        $to = $this->ensureInventoryItem($toWarehouse, $product);

        DB::transaction(function () use ($from, $to, $quantity) {
            $affected = InventoryItem::query()
                ->whereKey($from->id)
                ->where('quantity', '>=', $quantity)
                ->decrement('quantity', $quantity);

            if ($affected === 0) {
                throw new RuntimeException('Stok gudang asal tidak mencukupi untuk '.$product->name);
            }

            $to->increment('quantity', $quantity);
        });

        $this->movement(
            $product,
            InventoryMovement::TYPE_TRANSFER,
            $quantity,
            0,
            0,
            'TRANSFER',
            null,
            $actorId,
            $note,
            $fromWarehouse,
            null,
            null,
            $toWarehouse,
        );
    }

    /**
     * Map product-level movements to the default warehouse for backward compatibility.
     */
    private function decrementDefaultWarehouse(Product $product, int $quantity): void
    {
        $this->applyToDefaultWarehouse($product, -$quantity);
    }

    private function incrementDefaultWarehouse(Product $product, int $quantity): void
    {
        $this->applyToDefaultWarehouse($product, $quantity);
    }

    private function applyToDefaultWarehouse(Product $product, int $delta): void
    {
        $warehouse = $this->defaultWarehouse();
        $item = InventoryItem::firstOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
            ['quantity' => 0, 'min_threshold' => 0],
        );

        $item->update(['quantity' => max(0, $item->quantity + $delta)]);
    }

    private function ensureInventoryItem(Warehouse $warehouse, Product $product): InventoryItem
    {
        return InventoryItem::firstOrCreate(
            ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
            ['quantity' => 0, 'min_threshold' => 0],
        );
    }

    private function getInventoryItem(Warehouse $warehouse, Product $product): ?InventoryItem
    {
        return InventoryItem::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();
    }

    public function defaultWarehouse(): Warehouse
    {
        return Warehouse::where('is_active', true)->orderBy('id')->first()
            ?? Warehouse::create([
                'name' => 'Gudang Utama',
                'slug' => 'gudang-utama',
                'is_active' => true,
            ]);
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
        ?Warehouse $warehouse = null,
        ?Supplier $supplier = null,
        ?float $unitCost = null,
        ?Warehouse $toWarehouse = null,
    ): void {
        InventoryMovement::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse?->id,
            'to_warehouse_id' => $toWarehouse?->id,
            'supplier_id' => $supplier?->id,
            'type' => $type,
            'quantity' => $quantity,
            'before' => $before,
            'after' => $after,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'actor_id' => $actorId,
            'note' => $note,
            'unit_cost' => $unitCost,
            'created_at' => now(),
        ]);
    }
}
