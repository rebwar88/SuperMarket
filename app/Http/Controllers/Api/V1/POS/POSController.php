<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\POS;

use App\Domains\POS\Actions\CloseShiftAction;
use App\Domains\POS\Actions\OpenShiftAction;
use App\Domains\POS\Actions\ParkCartAction;
use App\Domains\POS\Actions\ProcessCheckoutAction;
use App\Domains\POS\Actions\ProcessReturnOrderAction;
use App\Domains\POS\Actions\ResumeCartAction;
use App\Domains\POS\Actions\ScanBarcodeAction;
use App\Domains\POS\DTOs\CheckoutData;
use App\Domains\POS\DTOs\ReturnOrderData;
use App\Domains\POS\DTOs\ShiftData;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class POSController extends Controller
{
    public function scan(Request $request, ScanBarcodeAction $action): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string'],
        ]);

        try {
            $scannedItem = $action->execute($validated['barcode']);
            return response()->json([
                'success' => true,
                'data' => $scannedItem->toArray(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function openShift(Request $request, OpenShiftAction $action): JsonResponse
    {
        $validated = $request->validate([
            'register_id' => ['required', 'string'],
            'opening_cash' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $data = ShiftData::fromArray([
                'register_id' => $validated['register_id'],
                'user_id' => $request->user()?->id ?? 'default-user',
                'opening_cash' => (float) $validated['opening_cash'],
            ]);

                        $openShift = \Illuminate\Support\Facades\DB::table('register_shifts')
                ->where('register_id', $validated['register_id'])
                ->where('user_id', $data->user_id)
                ->whereNull('closed_at')
                ->first();
            if (!$openShift) {
                throw new Exception('هیچ شیفتێکی کراوە نەدۆزرایەوە بۆ داخستن.');
            }
            $shift = $action->execute((string) $openShift->id, $data);

            return response()->json([
                'success' => true,
                'message' => 'شیفتی کاشێر بە سەرکەوتوویی کرایەوە.',
                'data' => $shift,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function closeShift(Request $request, CloseShiftAction $action): JsonResponse
    {
        $validated = $request->validate([
            'register_id' => ['required', 'string'],
            'closing_cash' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $data = ShiftData::fromArray([
                'register_id' => $validated['register_id'],
                'user_id' => $request->user()?->id ?? 'default-user',
                'closing_cash' => (float) $validated['closing_cash'],
            ]);

                        $openShift = \Illuminate\Support\Facades\DB::table('register_shifts')
                ->where('register_id', $validated['register_id'])
                ->where('user_id', $data->user_id)
                ->whereNull('closed_at')
                ->first();
            if (!$openShift) {
                throw new Exception('هیچ شیفتێکی کراوە نەدۆزرایەوە بۆ داخستن.');
            }
            $shift = $action->execute((string) $openShift->id, $data);

            return response()->json([
                'success' => true,
                'message' => 'شیفتی کاشێر بە سەرکەوتوویی داخرا.',
                'data' => $shift,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function checkout(Request $request, ProcessCheckoutAction $action): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'string'],
            'register_id' => ['required', 'string'],
            'register_shift_id' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'grand_total' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'change_due' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
        ]);

        try {
            $recalculatedSubtotal = 0.0;
            foreach ($validated['items'] as &$item) {
                $productId = $item['product_id'] ?? $item['id'] ?? null;
                if ($productId) {
                    $dbProduct = \Illuminate\Support\Facades\DB::table('products')->where('id', $productId)->first();
                    if ($dbProduct) {
                        $item['unit_price'] = (float) $dbProduct->retail_price;
                        $item['total_price'] = (float) ($item['unit_price'] * (float) ($item['quantity'] ?? 1));
                    }
                }
                $recalculatedSubtotal += (float) ($item['total_price'] ?? 0);
            }
            $validated['subtotal'] = $recalculatedSubtotal;
            $validated['grand_total'] = $recalculatedSubtotal - (float) ($validated['discount_amount'] ?? 0) + (float) ($validated['tax_amount'] ?? 0);

            $checkoutData = CheckoutData::fromArray(array_merge(
                $validated,
                ['user_id' => $request->user()?->id ?? 'default-user']
            ));

            $order = $action->execute($checkoutData);

            return response()->json([
                'success' => true,
                'message' => 'پسوولەی فرۆشتن بە سەرکەوتوویی بڕدرا و تۆمارکرا.',
                'data' => $order,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function parkCart(Request $request, ParkCartAction $action): JsonResponse
    {
        $validated = $request->validate([
            'register_id' => ['required', 'string'],
            'cart_data' => ['required', 'array'],
        ]);

        $parked = $action->execute(
            $validated['register_id'],
            $request->user()?->id ?? 'default-user',
            $validated['cart_data']
        );

        return response()->json([
            'success' => true,
            'message' => 'سەبەتەی کڕین بە سەرکەوتوویی ڕاگیرا (پارک کرا).',
            'data' => $parked,
        ], 201);
    }

    public function resumeCart(string $suspendedOrderId, ResumeCartAction $action): JsonResponse
    {
        try {
            $cartData = $action->execute($suspendedOrderId);

            return response()->json([
                'success' => true,
                'message' => 'سەبەتەی کڕین بە سەرکەوتوویی گەڕێندرایەوە.',
                'data' => $cartData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function processReturn(Request $request, ProcessReturnOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'original_order_id' => ['required', 'string'],
            'register_id' => ['required', 'string'],
            'register_shift_id' => ['required', 'string'],
            'refund_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
        ]);

        try {
            $returnData = ReturnOrderData::fromArray(array_merge(
                $validated,
                ['user_id' => $request->user()?->id ?? 'default-user']
            ));

            $returnOrder = $action->execute($returnData);

            return response()->json([
                'success' => true,
                'message' => 'پڕۆسەی گەڕاندنەوەی کاڵا بە سەرکەوتوویی ئەنجامدرا.',
                'data' => $returnOrder,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
