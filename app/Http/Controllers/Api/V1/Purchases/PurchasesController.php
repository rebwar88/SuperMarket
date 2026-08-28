<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Purchases;

use App\Domains\Purchases\Actions\CreatePurchaseOrderAction;
use App\Domains\Purchases\Actions\PaySupplierAction;
use App\Domains\Purchases\Actions\ReceiveGoodsAction;
use App\Domains\Purchases\Models\PurchaseOrder;
use App\Domains\Purchases\Models\Supplier;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchases\CreatePurchaseOrderRequest;
use App\Http\Requests\Purchases\PaySupplierRequest;
use App\Http\Requests\Purchases\ReceiveGoodsRequest;
use Exception;
use Illuminate\Http\JsonResponse;

class PurchasesController extends Controller
{
    public function indexOrders(): JsonResponse
    {
        $orders = PurchaseOrder::with(['supplier', 'items.product'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function createOrder(CreatePurchaseOrderRequest $request, CreatePurchaseOrderAction $action): JsonResponse
    {
        $order = $action->execute(
            supplierId: $request->validated('supplier_id'),
            storeId: $request->validated('store_id'),
            userId: $request->user()->id,
            items: $request->validated('items'),
            notes: $request->validated('notes')
        );

        return response()->json([
            'success' => true,
            'message' => 'داواکاریی کڕین بە سەرکەوتوویی دروستکرا.',
            'data' => $order,
        ], 201);
    }

    public function receiveGoods(ReceiveGoodsRequest $request, ReceiveGoodsAction $action): JsonResponse
    {
        try {
            $grn = $action->execute(
                purchaseOrderId: $request->validated('purchase_order_id'),
                warehouseId: $request->validated('warehouse_id'),
                userId: $request->user()->id,
                items: $request->validated('items')
            );

            return response()->json([
                'success' => true,
                'message' => 'کاڵاکان بە سەرکەوتوویی وەرگیراون و خرانە ناو ستۆکەوە.',
                'data' => $grn,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function paySupplier(PaySupplierRequest $request, PaySupplierAction $action): JsonResponse
    {
        try {
            $payment = $action->execute(
                supplierId: $request->validated('supplier_id'),
                amount: (float) $request->validated('amount'),
                paymentMethod: $request->validated('payment_method', 'cash'),
                reference: $request->validated('reference_number')
            );

            return response()->json([
                'success' => true,
                'message' => 'بڕی پارەدان بە دابینکار بە سەرکەوتوویی تۆمارکرا.',
                'data' => $payment,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function listSuppliers(): JsonResponse
    {
        $suppliers = Supplier::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $suppliers,
        ]);
    }
}
