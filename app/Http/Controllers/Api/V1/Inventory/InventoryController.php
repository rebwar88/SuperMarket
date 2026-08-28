<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Domains\Inventory\Actions\AddStockAction;
use App\Domains\Inventory\Actions\AdjustStockAction;
use App\Domains\Inventory\Actions\CreateProductAction;
use App\Domains\Inventory\Actions\CreateWastageAction;
use App\Domains\Inventory\Actions\TransferStockAction;
use App\Domains\Inventory\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\AddStockRequest;
use App\Http\Requests\Inventory\AdjustStockRequest;
use App\Http\Requests\Inventory\CreateProductRequest;
use App\Http\Requests\Inventory\CreateWastageRequest;
use App\Http\Requests\Inventory\TransferStockRequest;
use Exception;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with(['category', 'unit', 'barcodes', 'batches'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function storeProduct(CreateProductRequest $request, CreateProductAction $action): JsonResponse
    {
        $product = $action->execute(
            $request->except('barcodes'),
            $request->validated('barcodes', [])
        );

        return response()->json([
            'success' => true,
            'message' => 'کاڵای نوێ بە سەرکەوتوویی تۆمارکرا.',
            'data' => $product,
        ], 201);
    }

    public function addStock(AddStockRequest $request, AddStockAction $action): JsonResponse
    {
        $batch = $action->execute(
            productId: $request->validated('product_id'),
            warehouseId: $request->validated('warehouse_id'),
            quantity: (float) $request->validated('quantity'),
            purchaseCost: (float) $request->validated('purchase_cost'),
            batchNumber: $request->validated('batch_number'),
            expiryDate: $request->validated('expiry_date')
        );

        return response()->json([
            'success' => true,
            'message' => 'ستۆکی نوێ بە سەرکەوتوویی داخڵکرا.',
            'data' => $batch,
        ], 201);
    }

    public function transferStock(TransferStockRequest $request, TransferStockAction $action): JsonResponse
    {
        try {
            $targetBatch = $action->execute(
                sourceBatchId: $request->validated('source_batch_id'),
                targetWarehouseId: $request->validated('target_warehouse_id'),
                quantity: (float) $request->validated('quantity')
            );

            return response()->json([
                'success' => true,
                'message' => 'گواستنەوەی کاڵا لە نێوان مەخزەنەکان بە سەرکەوتوویی ئەنجامدرا.',
                'data' => $targetBatch,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function createWastage(CreateWastageRequest $request, CreateWastageAction $action): JsonResponse
    {
        try {
            $wastage = $action->execute(
                productId: $request->validated('product_id'),
                batchId: $request->validated('batch_id'),
                quantity: (float) $request->validated('quantity'),
                reason: $request->validated('reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'تەلەفی کاڵا بە سەرکەوتوویی تۆمارکرا و لە ستۆک کەمکرایەوە.',
                'data' => $wastage,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function adjustStock(AdjustStockRequest $request, AdjustStockAction $action): JsonResponse
    {
        try {
            $batch = $action->execute(
                batchId: $request->validated('batch_id'),
                newQuantity: (float) $request->validated('new_quantity'),
                reason: $request->validated('reason', 'manual_adjustment')
            );

            return response()->json([
                'success' => true,
                'message' => 'چاکسازیی ستۆک بە سەرکەوتوویی جێبەجێکرا.',
                'data' => $batch,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}