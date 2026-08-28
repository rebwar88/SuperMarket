<?php

declare(strict_types=1);

namespace App\Domains\POS\DTOs;

class CheckoutData
{
    public function __construct(
        public readonly string $store_id,
        public readonly string $register_id,
        public readonly string $register_shift_id,
        public readonly string $user_id,
        public readonly ?string $customer_id,
        public readonly float $subtotal,
        public readonly float $discount_amount,
        public readonly float $tax_amount,
        public readonly float $grand_total,
        public readonly float $paid_amount,
        public readonly float $change_due,
        public readonly string $payment_method,
        public readonly array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        $items = array_map(function ($item) {
            return is_array($item) ? (object) $item : $item;
        }, $data['items'] ?? []);

        return new self(
            store_id: $data['store_id'],
            register_id: $data['register_id'],
            register_shift_id: $data['register_shift_id'],
            user_id: $data['user_id'],
            customer_id: $data['customer_id'] ?? null,
            subtotal: (float) ($data['subtotal'] ?? 0.0),
            discount_amount: (float) ($data['discount_amount'] ?? 0.0),
            tax_amount: (float) ($data['tax_amount'] ?? 0.0),
            grand_total: (float) ($data['grand_total'] ?? 0.0),
            paid_amount: (float) ($data['paid_amount'] ?? 0.0),
            change_due: (float) ($data['change_due'] ?? 0.0),
            payment_method: $data['payment_method'] ?? 'cash',
            items: $items,
        );
    }
}
