<!DOCTYPE html>
<html lang="ckb" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ڕاپۆرتی کۆتایی ڕۆژ - Z-Report - {{ $settings['market_name'] ?? 'SuperMarket' }}</title>
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
        .subtitle { font-size: 10px; color: #444; margin-top: 2px; }
        .row { display: flex; justify-content: space-between; margin: 4px 0; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .total-row { font-size: 14px; font-weight: bold; }
        @media print {
            body { width: 100%; padding: 0; }
            @page { size: 80mm auto; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="title">{{ $settings['market_name'] ?? 'سوپەرمارکێت' }}</div>
        @if(!empty($settings['phone']) || !empty($settings['address']))
            <div class="subtitle">{{ $settings['phone'] ?? '' }} | {{ $settings['address'] ?? '' }}</div>
        @endif
        <div style="font-weight: bold; margin-top: 4px;">{{ $settings['receipt_header'] ?? 'ڕاپۆرتی کۆتایی شیفت (Z-REPORT)' }}</div>
        <div style="font-size: 11px;">کات: {{ date('Y-m-d H:i') }}</div>
    </div>

    <div class="row"><span>کاشێر:</span><span>{{ $shift->user->name ?? 'کاشێر' }}</span></div>
    <div class="row"><span>سندوق:</span><span>{{ $shift->register->name ?? 'REG-01' }}</span></div>
    <div class="row"><span>کردنەوەی شیفت:</span><span>{{ $shift->opened_at ? \Carbon\Carbon::parse($shift->opened_at)->format('m/d H:i') : '-' }}</span></div>
    <div class="row"><span>داخستنی شیفت:</span><span>{{ $shift->closed_at ? \Carbon\Carbon::parse($shift->closed_at)->format('m/d H:i') : 'کراوەیە' }}</span></div>

    <div class="divider"></div>

    <div class="row"><span>کۆی ژمارەی پسوولەکان:</span><span>{{ $orders->count() }}</span></div>
    <div class="row"><span>کاشی سەرەتای کردنەوە:</span><span>{{ number_format((float)$shift->opening_cash, 0) }} {{ $currencySymbol }}</span></div>
    
    <div class="divider"></div>

    <div class="row"><span>کۆی فرۆشی نەختینە (کاش):</span><span>{{ number_format((float)$totalCashSales, 0) }} {{ $currencySymbol }}</span></div>
    <div class="row"><span>کۆی فرۆشی قەرز:</span><span>{{ number_format((float)$totalCreditSales, 0) }} {{ $currencySymbol }}</span></div>
    <div class="row"><span>کۆی فرۆشی ئۆنلاین/کارت:</span><span>{{ number_format((float)$totalCardSales, 0) }} {{ $currencySymbol }}</span></div>
    @if($totalCodSales > 0)
        <div class="row"><span>کۆی گەیاندن (COD):</span><span>{{ number_format((float)$totalCodSales, 0) }} {{ $currencySymbol }}</span></div>
    @endif
    <div class="row"><span>کۆی داشکاندن:</span><span>{{ number_format((float)$totalDiscountsGiven, 0) }} {{ $currencySymbol }}</span></div>

    <div class="divider"></div>

    <div class="row total-row">
        <span>کۆی گشتی فرۆش:</span>
        <span>{{ number_format((float)($totalCashSales + $totalCreditSales + $totalCardSales + $totalCodSales), 0) }} {{ $currencySymbol }}</span>
    </div>
    <div class="row total-row" style="margin-top: 5px;">
        <span>کاشی پێویست لە سندوق:</span>
        <span>{{ number_format((float)($shift->opening_cash + $totalCashSales), 0) }} {{ $currencySymbol }}</span>
    </div>

    @if($shift->closing_cash !== null)
        <div class="row" style="color: #444; font-size: 11px; margin-top: 4px;">
            <span>کاشی ژمێردراوی کاشێر:</span>
            <span>{{ number_format((float)$shift->closing_cash, 0) }} {{ $currencySymbol }}</span>
        </div>
    @endif

    <div class="divider"></div>
    <div style="text-align: center; margin-top: 8px; font-size: 10px; line-height: 1.4;">
        {{ $settings['receipt_footer'] ?? 'سوپاس بۆ ماندووبوونتان' }}
    </div>

</body>
</html>
