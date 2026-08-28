<?php

declare(strict_types=1);

namespace App\Domains\Payments\Gateways;

use App\Domains\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class FibPaymentGateway implements PaymentGatewayInterface
{
    protected ?string $apiKey;
    protected ?string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('FIB_API_KEY');
        $this->secretKey = env('FIB_SECRET_KEY');
        $this->baseUrl = env('FIB_BASE_URL', 'https://api.fib.iq/v1');
    }

    public function isLiveApiAvailable(): bool
    {
        return !empty($this->apiKey) && !empty($this->secretKey);
    }

    public function initializePayment(float $amount, array $meta = []): array
    {
        if ($this->isLiveApiAvailable()) {
            try {
                // داواکاری فەرمی کاتێک API بەردەست دەبێت
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])->post("{$this->baseUrl}/payments/create", [
                    'amount' => $amount,
                    'currency' => 'IQD',
                    'callback_url' => route('api.payment.fib.callback'),
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'mode' => 'api_dynamic_qr',
                        'success' => true,
                        'qr_data' => $data['qr_code'] ?? null,
                        'gateway_transaction_id' => $data['payment_id'] ?? null,
                        'status' => 'pending',
                    ];
                }
            } catch (\Throwable $e) {}
        }

        // دۆخی خۆکار بۆ ئامێری دەستی ناو بازاڕ
        return [
            'mode' => 'manual_pos_entry',
            'success' => true,
            'amount' => $amount,
            'prompt_message' => 'تکایە بڕی پارەکە لەسەر ئامێری دەستیی FIB بنووسە و دوای دەرچوونی وەسڵ، ژمارەی مامەڵەکە (RRN) داخڵ بکە.',
        ];
    }

    public function checkStatus(string $transactionId): array
    {
        if ($this->isLiveApiAvailable()) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])->get("{$this->baseUrl}/payments/{$transactionId}/status");

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Throwable $e) {}
        }

        return ['status' => 'manual'];
    }
}
