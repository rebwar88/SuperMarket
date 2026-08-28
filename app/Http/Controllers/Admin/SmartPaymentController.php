<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Payments\Gateways\FibPaymentGateway;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\System\Models\SystemNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmartPaymentController extends Controller
{
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gateway' => ['required', 'string', 'in:fib,pos_card,fastpay'],
            'amount' => ['required', 'numeric', 'min:250'],
        ]);

        $gateway = $validated['gateway'];
        $amount = (float) $validated['amount'];

        $fibGateway = new FibPaymentGateway();
        $initData = $fibGateway->initializePayment($amount);

        $transaction = PaymentTransaction::create([
            'user_id' => (string) Auth::id(),
            'gateway' => $gateway,
            'amount' => $amount,
            'status' => 'pending',
            'gateway_transaction_id' => $initData['gateway_transaction_id'] ?? null,
            'qr_code_data' => $initData['qr_data'] ?? null,
            'payload' => $initData,
        ]);

        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
            'init_data' => $initData,
        ]);
    }

    public function confirmManual(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'exists:payment_transactions,id'],
            'reference_no' => ['required', 'string', 'min:3', 'max:50'],
            'card_bank' => ['nullable', 'string', 'max:100'],
        ]);

        $tx = PaymentTransaction::findOrFail($validated['transaction_id']);
        $tx->update([
            'reference_no' => $validated['reference_no'],
            'status' => 'completed',
            'payload' => array_merge($tx->payload ?? [], [
                'card_bank' => $validated['card_bank'] ?? 'Standard POS',
                'confirmed_by' => Auth::user()->name ?? 'Cashier',
            ]),
        ]);

        // ناردنی ئاگاداری ڕاستەوخۆ بۆ ئەدمین
        try {
            SystemNotification::create([
                'type' => 'digital_payment',
                'title' => 'پارەدانی سەرکەوتووی ' . strtoupper($tx->gateway),
                'message' => "بڕی " . number_format((float) $tx->amount, 0) . " د.ع بە سەرکەوتوویی لە ڕێگەی {$tx->gateway} وەرگیرا. ژمارەی پسوولە: #{$tx->reference_no}",
                'severity' => 'info',
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'پارەدانەکە بە سەرکەوتوویی تۆمارکرا.',
            'reference_no' => $tx->reference_no,
        ]);
    }

    public function checkApiStatus(int $id): JsonResponse
    {
        $tx = PaymentTransaction::findOrFail($id);
        $fibGateway = new FibPaymentGateway();

        if ($tx->gateway_transaction_id) {
            $status = $fibGateway->checkStatus($tx->gateway_transaction_id);
            if (($status['status'] ?? '') === 'PAID') {
                $tx->update(['status' => 'completed']);
                return response()->json(['status' => 'completed']);
            }
        }

        return response()->json(['status' => $tx->status]);
    }
}
