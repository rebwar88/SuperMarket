<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ڕاپۆرتی کۆتایی ڕۆژ - Z-Report</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace, 'Segoe UI', Tahoma;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            color: #000;
            background: #fff;
            font-size: 13px;
        }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; }
        .title { font-size: 16px; font-weight: bold; }
        .row { display: flex; justify-content: space-between; margin: 4px 0; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .total-row { font-size: 15px; font-weight: bold; }
        @media print {
            body { width: 100%; padding: 0; }
            @page { size: 80mm auto; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="title">{{ $marketName }}</div>
        <div>ڕاپۆرتی کۆتایی شیفت (Z-REPORT)</div>
        <div>کات: {{ date('Y-m-d H:i') }}</div>
    </div>

    <div class="row"><span>کاشێر:</span><span>{{ $shift->user->name ?? 'کاشێر' }}</span></div>
    <div class="row"><span>سندوق:</span><span>{{ $shift->register->name ?? 'REG-01' }}</span></div>
    <div class="row"><span>کردنەوەی شیفت:</span><span>{{ $shift->opened_at ? $shift->opened_at->format('H:i') : '-' }}</span></div>
    <div class="row"><span>داخستنی شیفت:</span><span>{{ $shift->closed_at ? $shift->closed_at->format('H:i') : 'کراوەیە' }}</span></div>

    <div class="divider"></div>

    <div class="row"><span>کۆی ژمارەی پسوولەکان:</span><span>{{ $orders->count() }}</span></div>
    <div class="row"><span>کاش لە کاتی کردنەوە:</span><span>{{ number_format((float)$shift->opening_cash, 0) }} د.ع</span></div>
    
    <div class="divider"></div>

    <div class="row"><span>کۆی فرۆشی نەختینە (کاش):</span><span>{{ number_format((float)$totalCashSales, 0) }} د.ع</span></div>
    <div class="row"><span>کۆی فرۆشی قەرز:</span><span>{{ number_format((float)$totalCreditSales, 0) }} د.ع</span></div>
    <div class="row"><span>کۆی فرۆشی کارت:</span><span>{{ number_format((float)$totalCardSales, 0) }} د.ع</span></div>
    <div class="row"><span>کۆی داشکاندنی بەخشراو:</span><span>{{ number_format((float)$totalDiscountsGiven, 0) }} د.ع</span></div>

    <div class="divider"></div>

    <div class="row total-row">
        <span>کۆی گشتی فرۆش:</span>
        <span>{{ number_format((float)($totalCashSales + $totalCreditSales + $totalCardSales), 0) }} د.ع</span>
    </div>
    <div class="row total-row">
        <span>کاشی پێویست لە سندوق:</span>
        <span>{{ number_format((float)($shift->opening_cash + $totalCashSales), 0) }} د.ع</span>
    </div>

    <div class="divider"></div>
    <div style="text-align: center; margin-top: 10px; font-size: 11px;">
        {{ $receiptFooter }}
    </div>

</body>
</html>
