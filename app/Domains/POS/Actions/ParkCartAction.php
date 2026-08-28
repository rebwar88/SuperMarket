<?php

declare(strict_types=1);

namespace App\Domains\POS\Actions;

use App\Domains\POS\DTOs\CartItemData;
use App\Domains\POS\Models\SuspendedOrder;
use Exception;

class ParkCartAction
{
    /**
     * @param array<CartItemData> $items
     */
    public function execute(string $registerId, string $userId, array $items): SuspendedOrder
    {
        if (empty($items)) {
            throw new Exception("ناتوانرێت عەرەبانەی بەتاڵ پارک بکرێت.");
        }

        $cartData = array_map(function ($item) {
            return $item instanceof CartItemData ? $item->toArray() : $item;
        }, $items);

        return SuspendedOrder::create([
            'register_id' => $registerId,
            'user_id' => $userId,
            'cart_data' => $cartData,
            'parked_at' => now(),
        ]);
    }
}
