<?php

declare(strict_types=1);

namespace App\Domains\POS\Actions;

use App\Domains\Inventory\Models\Barcode;
use App\Domains\Inventory\Models\Product;
use App\Domains\POS\DTOs\ScannedItemData;
use Exception;

class ScanBarcodeAction
{
    public function execute(string $barcodeString): ScannedItemData
    {
        $barcodeString = trim($barcodeString);

        // پشکنینی بارکۆدی کێشراوی تەرازوو (سەرەتای بە ٢١ یان ٢٠ دەستپێدەکات بە ١٢ یان ١٣ ڕەقەم)
        if (strlen($barcodeString) === 12 || strlen($barcodeString) === 13) {
            $prefix = substr($barcodeString, 0, 2);
            if (in_array($prefix, ['20', '21', '22', '23', '24'])) {
                return $this->parseScaleBarcode($barcodeString);
            }
        }

        // بارکۆدی ئاسایی
        $barcode = Barcode::with(['product.unit'])
            ->where('code', $barcodeString)
            ->first();

        if (! $barcode || ! $barcode->product) {
            // ئەگەر بارکۆد نەبوو، پشکنین بۆ SKU دەکەین
            $product = Product::with('unit')->where('sku', $barcodeString)->first();
            if (! $product) {
                throw new Exception("هیچ کاڵایەک بەم بارکۆدە نەدۆزرایەوە: {$barcodeString}");
            }

            return new ScannedItemData(
                productId: (string) $product->id,
                unitId: (string) $product->unit_id,
                name: $product->name,
                unitPrice: (float) $product->retail_price,
                quantity: 1.0,
                totalPrice: (float) $product->retail_price,
                isWeighted: false,
                barcode: $barcodeString
            );
        }

        $product = $barcode->product;
        $packingQty = (float) ($barcode->packing_qty ?: 1.0);
        $unitPrice = (float) $product->retail_price * $packingQty;

        return new ScannedItemData(
            productId: (string) $product->id,
            unitId: (string) ($barcode->unit_id ?: $product->unit_id),
            name: $product->name,
            unitPrice: $unitPrice,
            quantity: 1.0,
            totalPrice: $unitPrice,
            isWeighted: ($barcode->type === 'weight'),
            barcode: $barcodeString
        );
    }

    private function parseScaleBarcode(string $code): ScannedItemData
    {
        $itemCode = substr($code, 0, 7);
        $valuePart = (float) substr($code, 7, 5);

        // دۆزینەوەی کاڵای تەرازوو
        $barcode = Barcode::with('product')
            ->where('code', 'LIKE', substr($code, 0, 6) . '%')
            ->orWhere('code', $code)
            ->first();

        if (! $barcode || ! $barcode->product) {
            throw new Exception("کاڵای تەرازوو بەم کۆدە نەدۆزرایەوە: {$code}");
        }

        $product = $barcode->product;
        $unitPrice = (float) $product->retail_price;

        // ئەگەر ٥ ڕەقەمی کۆتایی نرخ بێت یان کێش (Weight: g / 1000)
        $weightInKg = $valuePart / 1000.0;
        if ($weightInKg <= 0) {
            $weightInKg = 1.0;
        }

        $totalPrice = round($weightInKg * $unitPrice, 2);

        return new ScannedItemData(
            productId: (string) $product->id,
            unitId: (string) ($barcode->unit_id ?: $product->unit_id),
            name: $product->name,
            unitPrice: $unitPrice,
            quantity: $weightInKg,
            totalPrice: $totalPrice,
            isWeighted: true,
            barcode: $code
        );
    }
}
