<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;

class DiscountService
{
    /**
     * Return the single best applicable automatic discount for an order's items,
     * per FR-PRI-003 (default: only one best automatic discount, no stacking).
     *
     * @return array{0: Discount|null, 1: float}
     */
    public function bestAutoDiscount(Collection $items, float $subtotal): array
    {
        $candidates = Discount::query()
            ->where('is_active', true)
            ->where('is_automatic', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();

        $best = null;
        $bestDiscount = 0.0;

        foreach ($candidates as $discount) {
            if ($subtotal < $discount->min_amount) {
                continue;
            }

            if ($discount->items()->exists()
                && ! $this->targetsMatch($discount, $items)) {
                continue;
            }

            $amount = $this->calculateDiscount($discount, $subtotal);

            if ($amount > $bestDiscount) {
                $bestDiscount = $amount;
                $best = $discount;
            }
        }

        return [$best, $bestDiscount];
    }

    public function applyCode(string $code, Collection $items, float $subtotal): array
    {
        $discount = Discount::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $discount) {
            return [null, 0.0];
        }

        if ($subtotal < $discount->min_amount) {
            return [null, 0.0];
        }

        if ($discount->items()->exists() && ! $this->targetsMatch($discount, $items)) {
            return [null, 0.0];
        }

        $amount = $this->calculateDiscount($discount, $subtotal);

        return [$discount, $amount];
    }

    public function calculateDiscount(Discount $discount, float $subtotal): float
    {
        if ($discount->max_amount !== null && $discount->max_amount > 0 && $subtotal > $discount->max_amount) {
            $subtotal = $discount->max_amount;
        }

        $amount = $discount->type === Discount::TYPE_PERCENTAGE
            ? $subtotal * ($discount->value / 100)
            : min($discount->value, $subtotal);

        $amount = round(max(0.0, $amount), 2);

        if ($discount->max_amount !== null) {
            $amount = min($amount, $discount->max_amount);
        }

        return min($amount, $subtotal);
    }

    private function targetsMatch(Discount $discount, Collection $items): bool
    {
        $targets = $discount->items()->get();

        $productIds = $items->pluck('product_id')->filter()->unique();

        $categoryIds = $productIds->isEmpty()
            ? collect()
            : Product::query()
                ->whereIn('id', $productIds)
                ->pluck('category_id')
                ->unique();

        foreach ($targets as $target) {
            if ($target->target_type === 'product' && $productIds->contains($target->target_id)) {
                return true;
            }

            if ($target->target_type === 'category' && $categoryIds->contains($target->target_id)) {
                return true;
            }
        }

        return false;
    }
}
