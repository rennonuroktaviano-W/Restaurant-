<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        protected PricingService $pricing,
        protected InventoryService $inventory,
        protected OrderNumberGenerator $orderNumber,
    ) {}

    /**
     * @param  array<int, array{product_id: int, quantity: int, notes?: string}>  $items
     * @return array{order: Order, created: bool}
     */
    public function checkout(array $items, string $orderType, array $data = [], ?string $idempotencyKey = null): array
    {
        if (empty($items)) {
            throw new RuntimeException('Keranjang kosong');
        }

        $this->validateLocation($orderType, $data);

        if ($idempotencyKey) {
            $existing = Order::where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                return ['order' => $existing, 'created' => false];
            }
        }

        return DB::transaction(function () use ($items, $orderType, $data, $idempotencyKey) {
            $productIds = collect($items)->pluck('product_id')->unique()->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get(['id', 'category_id', 'sku', 'name', 'sale_price', 'stock_type', 'stock', 'is_active', 'is_available', 'is_kitchen'])
                ->keyBy('id');

            $cartLines = [];

            foreach ($items as $line) {
                $product = $products->get($line['product_id']);

                if (! $product) {
                    throw new RuntimeException('Produk tidak ditemukan');
                }

                if (! $product->is_active || ! $product->is_available) {
                    throw new RuntimeException('Produk '.$product->name.' sedang tidak tersedia');
                }

                if ($product->isLimitedStock() && $product->stock < $line['quantity']) {
                    throw new RuntimeException('Stok '.$product->name.' tidak mencukupi');
                }

                $qty = max(1, (int) $line['quantity']);

                $cartLines[] = [
                    'product_id' => $product->id,
                    'category_id' => $product->category_id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'price' => (float) $product->sale_price,
                    'quantity' => $qty,
                    'subtotal' => round((float) $product->sale_price * $qty, 2),
                    'notes' => $line['notes'] ?? null,
                ];
            }

            $subtotal = round(
                array_sum(array_map(fn ($l) => $l['price'] * $l['quantity'], $cartLines)),
                2
            );

            $pricing = $this->pricing->calculate(collect($cartLines), $subtotal, $data['discount_code'] ?? null);

            $order = Order::create([
                'order_number' => $this->orderNumber->generate(),
                'order_type' => $orderType,
                'area_id' => $data['area_id'] ?? null,
                'table_id' => $data['table_id'] ?? null,
                'room_id' => $data['room_id'] ?? null,
                'has_kitchen_items' => $products->where('is_kitchen', true)->isNotEmpty(),
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'subtotal' => $pricing['subtotal'],
                'discount_amount' => $pricing['discount_amount'],
                'tax_amount' => $pricing['tax_amount'],
                'service_charge_amount' => $pricing['service_charge_amount'],
                'grand_total' => $pricing['grand_total'],
                'order_status' => Order::STATUS_NEW,
                'payment_status' => Order::PAYMENT_PENDING,
                'created_by' => $data['created_by'] ?? (auth()->id() ?: null),
                'idempotency_key' => $idempotencyKey,
                'ordered_at' => now(),
            ]);

            foreach ($cartLines as $line) {
                $order->items()->create($line);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => Order::STATUS_NEW,
                'actor_id' => $order->created_by,
                'created_at' => now(),
            ]);

            $this->inventory->reserve($order, $products);

            OrderCreated::dispatch($order);

            return ['order' => $order, 'created' => true];
        });
    }

    private function validateLocation(string $orderType, array $data): void
    {
        if ($orderType === Order::TYPE_DINE_IN) {
            if (empty($data['table_id'])) {
                throw new RuntimeException('Meja wajib dipilih untuk dine-in');
            }
        }

        if ($orderType === Order::TYPE_ROOM_SERVICE) {
            if (empty($data['room_id'])) {
                throw new RuntimeException('Room wajib dipilih untuk room service');
            }
        }
    }
}
