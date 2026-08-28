<?php

declare(strict_types=1);

namespace App\Domains\POS\DTOs;

class ScannedItemData
{
    public function __construct(
        public string $productId,
        public string $unitId,
        public string $name,
        public float $unitPrice,
        public float $quantity,
        public float $totalPrice,
        public bool $isWeighted = false,
        public ?string $barcode = null
    ) {}

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'unit_id' => $this->unitId,
            'name' => $this->name,
            'unit_price' => $this->unitPrice,
            'quantity' => $this->quantity,
            'total_price' => $this->totalPrice,
            'is_weighted' => $this->isWeighted,
            'barcode' => $this->barcode,
        ];
    }
}
