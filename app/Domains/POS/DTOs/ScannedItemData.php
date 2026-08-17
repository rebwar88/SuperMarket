<?php

declare(strict_types=1);

namespace App\Domains\POS\DTOs;

use App\Domains\Inventory\Models\Product;

class ScannedItemData
{
    public function __construct(
        public readonly Product $product,
        public readonly string $barcode,
        public readonly string $barcodeType,
        public readonly float $quantity,
        public readonly float $unitPrice,
        public readonly float $totalPrice,
        public readonly bool $isScaleItem = false
    ) {}

    public function toArray(): array
    {
        return [
            'product_id' => $this->product->id,
            'name' => $this->product->name,
            'sku' => $this->product->sku,
            'unit' => $this->product->unit?->short_code,
            'barcode' => $this->barcode,
            'barcode_type' => $this->barcodeType,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'total_price' => $this->totalPrice,
            'is_scale_item' => $this->isScaleItem,
        ];
    }
}