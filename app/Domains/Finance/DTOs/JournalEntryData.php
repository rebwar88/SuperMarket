<?php

declare(strict_types=1);

namespace App\Domains\Finance\DTOs;

class JournalEntryData
{
    public function __construct(
        public readonly ?string $order_id,
        public readonly ?string $purchase_order_id,
        public readonly string $source_type,
        public readonly array $lines,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            order_id: $data['order_id'] ?? null,
            purchase_order_id: $data['purchase_order_id'] ?? null,
            source_type: $data['source_type'] ?? 'pos_sale',
            lines: $data['lines'] ?? [],
        );
    }
}
