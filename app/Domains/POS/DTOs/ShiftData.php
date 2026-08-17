<?php

declare(strict_types=1);

namespace App\Domains\POS\DTOs;

class ShiftData
{
    public function __construct(
        public readonly string $registerId,
        public readonly string $userId,
        public readonly float $openingCash = 0.00,
        public readonly ?float $closingCash = null,
        public readonly ?string $reason = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            registerId: (string) $data['register_id'],
            userId: (string) $data['user_id'],
            openingCash: (float) ($data['opening_cash'] ?? 0.00),
            closingCash: isset($data['closing_cash']) ? (float) $data['closing_cash'] : null,
            reason: $data['reason'] ?? null
        );
    }
}