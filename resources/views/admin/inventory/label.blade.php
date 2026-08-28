<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>چاپی لەیبڵی بارکۆد - {{ $product->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .label-container {
            width: 50mm;
            height: 30mm;
            border: 1px dashed #ccc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2mm;
            box-sizing: border-box;
        }
        .product-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 46mm;
        }
        .price {
            font-size: 13px;
            font-weight: 900;
            margin-top: 2px;
        }
        svg {
            max-width: 44mm;
            height: 12mm;
        }
        @media print {
            body { padding: 0; }
            .label-container { border: none; }
            @page { size: 50mm 30mm; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="label-container">
        <div class="product-name">{{ $product->name }}</div>
        <svg id="barcode"></svg>
        <div class="price">{{ number_format((float) $product->retail_price, 0) }} IQD</div>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $barcode->code ?? $product->sku }}", {
            format: "CODE128",
            width: 1.4,
            height: 25,
            displayValue: true,
            fontSize: 9,
            margin: 0
        });
    </script>
</body>
</html>
