<?php

declare(strict_types=1);

namespace App\Domains\POS\DTOs;

class CartItemData
{
    public function __construct(
        public readonly string $productId,
        public readonly float $quantity,
        public readonly float $unitPrice,
        public readonly ?string $promotionId = null,
        public readonly ?string $barcode = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: (string) $data['product_id'],
            quantity: (float) $data['quantity'],
            unitPrice: (float) $data['unit_price'],
            promotionId: $data['promotion_id'] ?? null,
            barcode: $data['barcode'] ?? null
        );
    }

    public function getTotal(): float
    {
        return round($this->quantity * $this->unitPrice, 2);
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'total_price' => $this->getTotal(),
            'promotion_id' => $this->promotionId,
            'barcode' => $this->barcode,
        ];
    }
}