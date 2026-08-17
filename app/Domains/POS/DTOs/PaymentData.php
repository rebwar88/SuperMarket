<?php

declare(strict_types=1);

namespace App\Domains\POS\DTOs;

class PaymentData
{
    public function __construct(
        public readonly string $method, // cash, card, debt, loyalty_points
        public readonly float $amount,
        public readonly string $currency = 'IQD',
        public readonly float $exchangeRate = 1.0000,
        public readonly ?string $referenceNumber = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            method: (string) $data['method'],
            amount: (float) $data['amount'],
            currency: (string) ($data['currency'] ?? 'IQD'),
            exchangeRate: (float) ($data['exchange_rate'] ?? 1.0000),
            referenceNumber: $data['reference_number'] ?? null
        );
    }

    public function getAmountInBaseCurrency(): float
    {
        return round($this->amount * $this->exchangeRate, 2);
    }
}