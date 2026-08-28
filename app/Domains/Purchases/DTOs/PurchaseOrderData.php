<?php

declare(strict_types=1);

namespace App\Domains\Purchases\DTOs;

final class PurchaseOrderData
{
    public function __construct(
        public readonly string $supplierId,
        public readonly string $warehouseId,
        public readonly string $userId,
        public readonly float $totalAmount,
        public readonly array $items,
        public readonly ?string $notes = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            supplierId: (string) $data['supplier_id'],
            warehouseId: (string) $data['warehouse_id'],
            userId: (string) $data['user_id'],
            totalAmount: (float) ($data['total_amount'] ?? 0.00),
            items: $data['items'] ?? [],
            notes: $data['notes'] ?? null
        );
    }
}
