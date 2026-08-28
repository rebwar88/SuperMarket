<?php

declare(strict_types=1);

namespace App\Domains\Pricing\Services;

use App\Domains\Pricing\Models\Promotion;

class PromotionEngineService
{
    /**
     * لێکدانی داشکاندن بەپێی جۆری پرۆمۆشن و بڕ و نرخی کاڵا
     */
    public function calculateItemDiscount(Promotion $promotion, float $quantity, float $unitPrice): float
    {
        if (!$promotion->is_active) {
            return 0.00;
        }

        $now = now();
        if ($promotion->starts_at && $now->lt($promotion->starts_at)) {
            return 0.00;
        }
        if ($promotion->ends_at && $now->gt($promotion->ends_at)) {
            return 0.00;
        }

        $lineTotal = $quantity * $unitPrice;

        return match ($promotion->type) {
            'percentage' => round($lineTotal * ((float) $promotion->discount_value / 100), 2),
            'fixed_discount' => round(min($lineTotal, (float) $promotion->discount_value * $quantity), 2),
            'bogo' => round(floor($quantity / 2) * $unitPrice, 2),
            default => 0.00,
        };
    }
}
