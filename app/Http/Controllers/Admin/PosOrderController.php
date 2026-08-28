<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PosOrderController extends Controller
{
    /**
     * هێنانی لیستی ئەو وەسڵانەی کە تەنها ئەم کاشێرە خۆی تۆماری کردوون
     */
    public function myInvoices(): JsonResponse
    {
        $userId = (string) (Auth::id() ?? DB::table('users')->value('id'));

        // هێنانی وەسڵەکان بە تەنها بۆ ئەم کاشێرە
        $orders = DB::table('orders')
            ->where('user_id', $userId)
            ->latest('created_at')
            ->take(30)
            ->get();

        $result = $orders->map(function ($order) {
            $items = DB::table('order_items')
                ->where('order_id', $order->id)
                ->get();

            return [
                'id' => $order->id,
                'invoice_no' => $order->invoice_number ?? $order->invoice_no,
                'grand_total' => (float) $order->grand_total,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'reference_no' => $order->reference_no,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_address' => $order->customer_address,
                'created_at' => $order->created_at,
                'items_count' => $items->count(),
                'items' => $items->map(fn($item) => [
                    'name' => $item->product_name ?? 'کاڵا',
                    'qty' => (int) $item->quantity,
                    'price' => (float) $item->unit_price,
                    'total' => (float) $item->total_price,
                ]),
            ];
        });

        return response()->json([
            'success' => true,
            'orders' => $result,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => ['required', 'array', 'min:1'],
                'items.*.id' => ['nullable'],
                'items.*.name' => ['required', 'string'],
                'items.*.price' => ['required', 'numeric', 'min:0'],
                'items.*.qty' => ['required', 'numeric', 'min:1'],
                'payment_method' => ['required', 'string'],
                'reference_no' => ['nullable', 'string'],
                'is_cod' => ['nullable'],
                'customer_name' => ['nullable', 'string', 'max:255'],
                'customer_phone' => ['nullable', 'string', 'max:50'],
                'customer_address' => ['nullable', 'string', 'max:500'],
            ]);

            return DB::transaction(function () use ($validated) {
                $user = Auth::user();
                $userId = (string) ($user->id ?? DB::table('users')->value('id'));

                $store = DB::table('stores')->first();
                $storeId = (string) ($store->id ?? Str::uuid());

                $shift = DB::table('register_shifts')
                    ->where('user_id', $userId)
                    ->where('status', 'open')
                    ->latest('opened_at')
                    ->first();

                if (!$shift) {
                    $register = DB::table('registers')->first();
                    $registerId = (string) ($register->id ?? Str::uuid());
                    
                    $shiftId = (string) Str::uuid();
                    DB::table('register_shifts')->insert([
                        'id' => $shiftId,
                        'register_id' => $registerId,
                        'user_id' => $userId,
                        'opening_cash' => 0,
                        'status' => 'open',
                        'opened_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $shiftId = (string) $shift->id;
                }

                $method = $validated['payment_method'];
                $isCod = !empty($validated['is_cod']) && $validated['is_cod'] !== 'false';
                
                if ($isCod) {
                    $method = 'cod';
                }

                $paymentStatus = match ($method) {
                    'pay_now', 'cash', 'pay_online', 'online' => 'paid',
                    'pay_later', 'debt' => 'debt',
                    'cod' => 'pending',
                    default => 'paid',
                };

                $dbMethod = match ($method) {
                    'pay_now' => 'cash',
                    'pay_online' => 'online',
                    'pay_later' => 'debt',
                    'cod' => 'cod',
                    default => $method,
                };

                $total = 0;
                foreach ($validated['items'] as $item) {
                    $total += ((float)$item['price'] * (int)$item['qty']);
                }

                $invoiceNumber = ($isCod ? 'COD-' : 'INV-') . strtoupper(Str::random(8));
                $orderUuid = (string) Str::uuid();
                $defaultDbProductId = DB::table('products')->value('id') ?? (string) Str::uuid();

                DB::table('orders')->insert([
                    'id' => $orderUuid,
                    'invoice_number' => $invoiceNumber,
                    'invoice_no' => $invoiceNumber,
                    'store_id' => $storeId,
                    'register_shift_id' => $shiftId,
                    'customer_id' => null,
                    'user_id' => $userId,
                    'subtotal' => $total,
                    'discount_amount' => 0,
                    'discount' => 0,
                    'tax_amount' => 0,
                    'grand_total' => $total,
                    'paid_amount' => ($paymentStatus === 'paid' ? $total : 0),
                    'change_due' => 0,
                    'status' => 'completed',
                    'payment_method' => $dbMethod,
                    'payment_status' => $paymentStatus,
                    'reference_no' => $validated['reference_no'] ?? null,
                    'customer_name' => $validated['customer_name'] ?? null,
                    'customer_phone' => $validated['customer_phone'] ?? null,
                    'customer_address' => $validated['customer_address'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($validated['items'] as $item) {
                    $pId = $item['id'] ?? null;
                    if (!$pId || !DB::table('products')->where('id', $pId)->exists()) {
                        $pId = $defaultDbProductId;
                    }

                    DB::table('order_items')->insert([
                        'id' => (string) Str::uuid(),
                        'order_id' => $orderUuid,
                        'product_id' => (string) $pId,
                        'promotion_id' => null,
                        'quantity' => (int) $item['qty'],
                        'unit_price' => (float) $item['price'],
                        'total_price' => (float) $item['price'] * (int) $item['qty'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($dbMethod === 'online' || !empty($validated['reference_no'])) {
                    DB::table('payment_transactions')->insert([
                        'order_id' => $orderUuid,
                        'user_id' => $userId,
                        'gateway' => 'pos_card',
                        'amount' => $total,
                        'currency' => 'IQD',
                        'status' => 'completed',
                        'reference_no' => $validated['reference_no'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'order_id' => $orderUuid,
                    'invoice_no' => $invoiceNumber,
                    'grand_total' => $total,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('POS Order Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
