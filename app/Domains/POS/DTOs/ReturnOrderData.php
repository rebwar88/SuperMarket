<?php

declare(strict_types=1);

namespace App\Domains\POS\DTOs;

class ReturnOrderData
{
    /**
     * @param array<int, array{product_id: string, quantity: float, refund_price: float, condition: string}> $items
     */
    public function __construct(
        public readonly string $orderId,
        public readonly string $userId,
        public readonly array $items,
        public readonly ?string $reason = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: (string) $data['order_id'],
            userId: (string) $data['user_id'],
            items: $data['items'] ?? [],
            reason: $data['reason'] ?? null
        );
    }

    public function getTotalRefund(): float
    {
        return round(array_sum(array_map(
            fn(array $item) => (float) $item['quantity'] * (float) $item['refund_price'],
            $this->items
        )), 2);
    }
}