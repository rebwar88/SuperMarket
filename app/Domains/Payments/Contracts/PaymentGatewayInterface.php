<?php

declare(strict_types=1);

namespace App\Domains\Payments\Contracts;

interface PaymentGatewayInterface
{
    public function initializePayment(float $amount, array $meta = []): array;
    public function checkStatus(string $transactionId): array;
}
