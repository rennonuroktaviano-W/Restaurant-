<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeeklyPromoService
{
    protected const CODE_WORDS = ['HEBAT', 'PROMO', 'SPESIAL', 'MANTAP', 'HEMAT', 'BARU'];

    protected const TARGET_PRODUCTS = 2;

    /**
     * Return this week's boundaries (Monday 00:00 -> next Monday 00:00) in the app timezone.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function weekRange(?Carbon $date = null): array
    {
        $start = ($date ?? now())->copy()->startOfWeek(Carbon::MONDAY);
        $end = $start->copy()->addWeek();

        return [$start, $end];
    }

    public function current(): ?Discount
    {
        [$start, $end] = $this->weekRange();

        return Discount::query()
            ->where('is_weekly', true)
            ->where('is_active', true)
            ->where('starts_at', $start)
            ->where('ends_at', $end)
            ->first();
    }

    /**
     * Return the active weekly promo for the current week, creating one when missing.
     */
    public function ensureCurrent(): Discount
    {
        return $this->current() ?? $this->createFor($this->weekRange()[0]);
    }

    /**
     * Replace the current week's weekly promo with a freshly randomised one.
     */
    public function regenerate(): Discount
    {
        return $this->createFor($this->weekRange()[0]);
    }

    public function targetProductNames(Discount $promo): Collection
    {
        $targetIds = $promo->items()
            ->where('target_type', 'product')
            ->pluck('target_id');

        return Product::query()
            ->whereIn('id', $targetIds)
            ->pluck('name');
    }

    protected function createFor(Carbon $weekStart): Discount
    {
        $this->deactivateActiveWeekly();

        $value = random_int(10, 30);

        $discount = Discount::create([
            'name' => 'Promo Mingguan',
            'description' => 'Diskon '.$value.'% untuk produk pilihan minggu ini. Gunakan kode promo saat checkout.',
            'code' => $this->uniqueCode($value),
            'type' => Discount::TYPE_PERCENTAGE,
            'value' => $value,
            'min_amount' => 0,
            'max_amount' => null,
            'is_automatic' => false,
            'is_weekly' => true,
            'starts_at' => $weekStart,
            'ends_at' => $weekStart->copy()->addWeek(),
            'is_active' => true,
        ]);

        $this->attachRandomProducts($discount, self::TARGET_PRODUCTS);

        return $discount;
    }

    protected function deactivateActiveWeekly(): void
    {
        Discount::query()
            ->where('is_weekly', true)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    protected function uniqueCode(int $value): string
    {
        $suffix = str_pad((string) $value, 2, '0', STR_PAD_LEFT);

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $word = self::CODE_WORDS[array_rand(self::CODE_WORDS)];
            $code = $word.$suffix;

            if (! Discount::query()->where('code', $code)->exists()) {
                return $code;
            }
        }

        return 'HEBAT'.$suffix.strtoupper(bin2hex(random_bytes(2)));
    }

    protected function attachRandomProducts(Discount $discount, int $count): void
    {
        $products = Product::visible()
            ->inRandomOrder()
            ->limit($count)
            ->pluck('id');

        foreach ($products as $productId) {
            $discount->items()->create([
                'target_type' => 'product',
                'target_id' => $productId,
            ]);
        }
    }
}
