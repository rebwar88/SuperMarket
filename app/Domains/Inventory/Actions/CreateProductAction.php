<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Actions;

use App\Domains\Inventory\Models\Barcode;
use App\Domains\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{
    public function execute(array $productData, array $barcodes = []): Product
    {
        return DB::transaction(function () use ($productData, $barcodes) {
            $product = Product::create($productData);

            foreach ($barcodes as $barcodeItem) {
                Barcode::create([
                    'product_id' => $product->id,
                    'code' => $barcodeItem['code'],
                    'type' => $barcodeItem['type'] ?? 'unit',
                    'packing_qty' => $barcodeItem['packing_qty'] ?? 1.0,
                ]);
            }

            return $product->load(['barcodes', 'unit', 'category']);
        });
    }
}