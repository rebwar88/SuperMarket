<?php

declare(strict_types=1);

namespace App\Domains\Pricing\DTOs;

final class PromotionData
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly float $discountValue,
        public readonly bool $isActive = true,
        public readonly ?string $startsAt = null,
        public readonly ?string $endsAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            type: (string) $data['type'],
            discountValue: (float) ($data['discount_value'] ?? 0.00),
            isActive: (bool) ($data['is_active'] ?? true),
            startsAt: $data['starts_at'] ?? null,
            endsAt: $data['ends_at'] ?? null
        );
    }
}
