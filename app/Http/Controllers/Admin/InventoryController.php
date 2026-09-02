<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Inventory\Actions\AddStockAction;
use App\Domains\Inventory\Actions\CreateProductAction;
use App\Domains\Inventory\Models\Category;
use App\Domains\Inventory\Models\Product;
use App\Domains\Inventory\Models\Unit;
use App\Domains\Organization\Models\Warehouse;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        // ١. هێنانی ڕێکخستنەکان
        $settingsRaw = DB::table('store_settings')->pluck('value', 'key')->toArray();
        $defaults = [
            'market_name' => 'سوپەرمارکێت',
            'currency_symbol' => 'د.ع',
            'low_stock_alert' => '5',
        ];
        $settings = array_merge($defaults, $settingsRaw);

        $products = Product::with(['category', 'unit', 'barcodes'])
            ->latest()
            ->paginate(15);

        // ٢. هەژمارکردنی ستۆکی ئێستای هەر کاڵایەک لە خشتەی batches یان stock_batches
        $stockCounts = collect();
        if (Schema::hasTable('batches')) {
            $stockCounts = DB::table('batches')
                ->select('product_id', DB::raw('SUM(stock_qty) as total_qty'))
                ->groupBy('product_id')
                ->pluck('total_qty', 'product_id');
        } elseif (Schema::hasTable('stock_batches')) {
            $stockCounts = DB::table('stock_batches')
                ->select('product_id', DB::raw('SUM(remaining_quantity) as total_qty'))
                ->groupBy('product_id')
                ->pluck('total_qty', 'product_id');
        }

        foreach ($products as $product) {
            $product->current_stock = (float) ($stockCounts[$product->id] ?? 0);
        }

        $categories = Category::all();
        $units = Unit::all();
        $warehouses = Warehouse::all();

        return view('admin.inventory.index', compact('products', 'categories', 'units', 'warehouses', 'settings'));
    }

    public function storeProduct(Request $request, CreateProductAction $createProduct, AddStockAction $addStock): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'category_id' => ['required', 'string'],
            'unit_id' => ['required', 'string'],
            'retail_price' => ['required', 'numeric', 'min:0'],
            'barcode' => ['required', 'string', 'max:50', 'unique:barcodes,code'],
            'barcode_type' => ['required', 'in:unit,weight,pack'],
            'initial_stock' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'warehouse_id' => ['nullable', 'string'],
        ], [
            'sku.unique' => 'ئەم SKUـیە پێشتر بەکارهاتووە.',
            'barcode.unique' => 'ئەم بارکۆدە پێشتر بۆ کاڵایەکی تر تۆمارکراوە.',
        ]);

        $product = $createProduct->execute([
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'category_id' => $validated['category_id'],
            'unit_id' => $validated['unit_id'],
            'retail_price' => (float) $validated['retail_price'],
        ], [
            [
                'code' => $validated['barcode'],
                'type' => $validated['barcode_type'],
                'packing_qty' => 1.0,
            ]
        ]);

        if (!empty($validated['initial_stock']) && (float) $validated['initial_stock'] > 0) {
            $warehouseId = $validated['warehouse_id'] ?? Warehouse::first()?->id;
            if ($warehouseId) {
                $batchCode = 'INIT-' . strtoupper(substr(uniqid(), -5));
                $addStock->execute(
                    $product->id,
                    $warehouseId,
                    (float) $validated['initial_stock'],
                    (float) ($validated['cost_price'] ?? 0),
                    $batchCode
                );
            }
        }

        return redirect()->route('admin.inventory.index')->with('success', 'کاڵاکە بە سەرکەوتوویی زیادکرا.');
    }

    public function storePurchase(Request $request, AddStockAction $addStock): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'string', 'exists:products,id'],
            'warehouse_id' => ['required', 'string', 'exists:warehouses,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'batch_code' => ['nullable', 'string', 'max:50'],
        ]);

        $batchCode = $validated['batch_code'] ?: 'BATCH-' . date('Ymd') . '-' . rand(100, 999);

        $addStock->execute(
            $validated['product_id'],
            $validated['warehouse_id'],
            (float) $validated['quantity'],
            (float) $validated['cost_price'],
            $batchCode
        );

        return redirect()->route('admin.inventory.index')->with('success', 'پسوولەی کڕین تۆمارکرا و ستۆک نوێکرایەوە.');
    }

    public function printLabel(string $id): View
    {
        $product = Product::with(['barcodes', 'unit'])->findOrFail($id);
        $barcode = $product->barcodes->first();

        return view('admin.inventory.label', compact('product', 'barcode'));
    }
}
