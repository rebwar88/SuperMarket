<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domains\POS\Actions\ProcessCheckoutAction;
use App\Domains\POS\DTOs\CheckoutData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PosOrderController extends Controller
{
    public function __construct(
        private readonly ProcessCheckoutAction $checkoutAction
    ) {}

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $items = $request->input('items', []);

            if (empty($items)) {
                return response()->json(['success' => false, 'message' => 'کابینەی فرۆشتن بەتاڵە'], 400);
            }

            // ١. دۆزینەوەی شیفتی کراوەی ئەم کاشێرە
            $shift = DB::table('register_shifts')
                ->where('user_id', $user->id)
                ->whereNull('closed_at')
                ->first();

            if (!$shift) {
                // ئەگەر شیفتی نەبوو، بە خێرایی بۆی دروست دەکەین
                $register = DB::table('registers')->first();
                $shiftId = Str::uuid()->toString();
                DB::table('register_shifts')->insert([
                    'id' => $shiftId,
                    'user_id' => $user->id,
                    'register_id' => $register->id ?? null,
                    'opened_at' => now(),
                    'opening_cash' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $shift = clone (object) ['id' => $shiftId, 'register_id' => $register->id ?? null];
            }

            $storeId = DB::table('registers')->where('id', $shift->register_id)->value('store_id');

            // ٢. دروستکردن یان دۆزینەوەی کڕیار ئەگەر ناوی نێردرابوو (بۆ مەبەستی قەرز)
            $customerId = null;
            if (!empty($request->customer_name)) {
                $customer = DB::table('customers')->where('phone', $request->customer_phone)->first();
                if ($customer) {
                    $customerId = $customer->id;
                } else {
                    $customerId = Str::uuid()->toString();
                    DB::table('customers')->insert([
                        'id' => $customerId,
                        'name' => $request->customer_name,
                        'phone' => $request->customer_phone,
                        'total_debt' => 0,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // ٣. حیسابکردنی پارێزراوی کۆی گشتی لەسەر بنەمای کاڵاکان
            $subtotal = 0.0;
            $processedItems = [];

            foreach ($items as $item) {
                // وەرگرتنی ئایدی و نرخ بەپێی ئەوەی جاڤاسکریپتەکە چۆن دەینێرێت
                $productId = $item['product_id'] ?? $item['id'] ?? null;
                if (!$productId) continue;

                $product = DB::table('products')->where('id', $productId)->first();
                if (!$product) continue;

                $qty = (float) ($item['quantity'] ?? 1);
                $price = (float) $product->retail_price;

                $rowTotal = $qty * $price;
                $subtotal += $rowTotal;

                $processedItems[] = [
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $rowTotal
                ];
            }

            if (empty($processedItems)) {
                return response()->json(['success' => false, 'message' => 'هیچ کاڵایەکی دروست نەدۆزرایەوە'], 400);
            }

            // ٤. دیاریکردنی شێوازی پارەدان و بڕی قەرز
            $paymentMethod = strtolower((string) $request->input('payment_method', 'cash'));
            
            // ئەگەر نەقد بوو بڕی پارەکە تەواوە، ئەگەر قەرز بوو بڕی وەرگیراو سفرە
            $paidAmount = ($request->is_cod || in_array($paymentMethod, ['debt', 'credit'])) ? 0.0 : $subtotal;

            // ٥. پڕکردنەوەی DTO بۆ ناردنی بۆ ڕێڕەوە سەرەکییەکەی ProcessCheckoutAction
            $dto = CheckoutData::fromArray([
                'store_id' => $storeId ?? Str::uuid()->toString(),
                'register_id' => (string) $shift->register_id,
                'register_shift_id' => (string) $shift->id,
                'user_id' => (string) $user->id,
                'customer_id' => $customerId,
                'subtotal' => $subtotal,
                'discount_amount' => 0.0,
                'tax_amount' => 0.0,
                'grand_total' => $subtotal,
                'paid_amount' => $paidAmount,
                'change_due' => 0.0,
                'payment_method' => $paymentMethod,
                'items' => $processedItems,
            ]);

            // ٦. جێبەجێکردنی پرۆسەکە (بڕینی ستۆک، قەیدی ژمێریاری، و باڵانسی قەرز بە یەکجار)
            $order = $this->checkoutAction->execute($dto);

            return response()->json([
                'success' => true,
                'invoice_no' => $order->invoice_number,
                'grand_total' => $order->grand_total,
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('POS Checkout Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'هەڵەیەک ڕوویدا لە کاتی جێبەجێکردنی فرۆشتنەکە: ' . $e->getMessage()
            ], 500);
        }
    }
}
