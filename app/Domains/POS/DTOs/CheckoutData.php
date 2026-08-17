<?php

declare(strict_types=1);

namespace App\Domains\POS\DTOs;

class CheckoutData
{
    /**
     * @param CartItemData[] $items
     * @param PaymentData[] $payments
     */
    public function __construct(
        public readonly string $storeId,
        public readonly string $registerShiftId,
        public readonly string $userId,
        public readonly array $items,
        public readonly array $payments,
        public readonly ?string $customerId = null,
        public readonly float $discountAmount = 0.00,
        public readonly float $taxAmount = 0.00
    ) {}

    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn(array $item) => CartItemData::fromArray($item),
            $data['items'] ?? []
        );

        $payments = array_map(
            fn(array $payment) => PaymentData::fromArray($payment),
            $data['payments'] ?? []
        );

        return new self(
            storeId: (string) $data['store_id'],
            registerShiftId: (string) $data['register_shift_id'],
            userId: (string) $data['user_id'],
            items: $items,
            payments: $payments,
            customerId: $data['customer_id'] ?? null,
            discountAmount: (float) ($data['discount_amount'] ?? 0.00),
            taxAmount: (float) ($data['tax_amount'] ?? 0.00)
        );
    }

    public function getSubtotal(): float
    {
        return round(array_sum(array_map(fn(CartItemData $item) => $item->getTotal(), $this->items)), 2);
    }

    public function getGrandTotal(): float
    {
        return round($this->getSubtotal() - $this->discountAmount + $this->taxAmount, 2);
    }

    public function getTotalPaid(): float
    {
        return round(array_sum(array_map(fn(PaymentData $p) => $p->getAmountInBaseCurrency(), $this->payments)), 2);
    }
}