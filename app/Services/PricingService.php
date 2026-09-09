<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PricingService
{
    public function __construct(
        protected SettingsService $settings,
        protected DiscountService $discountService,
    ) {}

    /**
     * Server-side price calculation per FR-PRI-001..006.
     * Order of calculation: subtotal -> discount -> taxable base -> tax -> service charge -> grand total.
     *
     * @param  Collection<int, array{product_id: int, product_name: string, price: float, quantity: int}>  $items
     */
    public function calculate(Collection $items, float $subtotal, ?string $code = null): array
    {
        [$autoDiscount, $autoAmount] = $this->discountService->bestAutoDiscount($items, $subtotal);

        $codeDiscount = null;
        $codeAmount = 0.0;

        if ($code) {
            [$codeDiscount, $codeAmount] = $this->discountService->applyCode($code, $items, $subtotal);
        }

        [$discount, $discountAmount] = $codeAmount > $autoAmount
            ? [$codeDiscount, $codeAmount]
            : [$autoDiscount, $autoAmount];

        $taxRate = (float) $this->settings->get('pricing.tax_rate', 0);
        $serviceRate = (float) $this->settings->get('pricing.service_charge_rate', 0);

        $taxableBase = max(0.0, $subtotal - $discountAmount);
        $taxAmount = round($taxableBase * ($taxRate / 100), 2);
        $serviceAmount = round($taxableBase * ($serviceRate / 100), 2);
        $grandTotal = round($taxableBase + $taxAmount + $serviceAmount, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_amount' => $taxAmount,
            'service_charge_amount' => $serviceAmount,
            'grand_total' => $grandTotal,
            'taxable_base' => $taxableBase,
            'discount' => $discount,
            'discount_code' => $codeDiscount?->code,
        ];
    }
}
