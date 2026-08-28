<?php

declare(strict_types=1);

namespace App\Domains\Pricing\Actions;

use App\Domains\Pricing\Models\Promotion;
use App\Domains\Pricing\Services\PromotionEngineService;

class ApplyPromotionsAction
{
    public function __construct(
        private readonly PromotionEngineService $promotionEngine
    ) {}

    /**
     * جێبەجێکردنی پرۆمۆشن لەسەر لیستێک لە کاڵاکان
     * @param array $items [ ['product_id' => '...', 'quantity' => 2, 'unit_price' => 1000, 'promotion_id' => '...'] ]
     * @return array [ 'items' => [...], 'total_discount' => 500.00 ]
     */
    public function execute(array $items): array
    {
        $totalDiscount = 0.00;
        $processedItems = [];

        foreach ($items as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $itemDiscount = 0.00;

            if (!empty($item['promotion_id'])) {
                $promotion = Promotion::find($item['promotion_id']);
                if ($promotion) {
                    $itemDiscount = $this->promotionEngine->calculateItemDiscount($promotion, $quantity, $unitPrice);
                }
            }

            $totalPrice = ($quantity * $unitPrice) - $itemDiscount;
            $totalDiscount += $itemDiscount;

            $item['discount_amount'] = $itemDiscount;
            $item['total_price'] = max(0.00, $totalPrice);
            $processedItems[] = $item;
        }

        return [
            'items' => $processedItems,
            'total_discount' => round($totalDiscount, 2),
        ];
    }
}
