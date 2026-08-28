<?php

declare(strict_types=1);

namespace App\Domains\POS\Actions;

use App\Domains\POS\DTOs\CartItemData;
use App\Domains\POS\Models\SuspendedOrder;

class ResumeCartAction
{
    /**
     * @return array<CartItemData>
     */
    public function execute(string $suspendedOrderId): array
    {
        $suspendedOrder = SuspendedOrder::findOrFail($suspendedOrderId);

        $cartData = is_string($suspendedOrder->cart_data)
            ? json_decode($suspendedOrder->cart_data, true)
            : $suspendedOrder->cart_data;

        $items = array_map(function (array $item) {
            return CartItemData::fromArray($item);
        }, $cartData ?? []);

        // سڕینەوە لە هەڵپەسێردراوەکان پاش دەرهێنانەوە
        $suspendedOrder->delete();

        return $items;
    }
}
