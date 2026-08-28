<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\POS\Models\Order;
use App\Domains\POS\Models\RegisterShift;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function zReport(string $shiftId): View
    {
        $shift = RegisterShift::with(['user', 'register'])->findOrFail($shiftId);

        // ١. هێنانی ڕێکخستنەکان
        $settingsRaw = DB::table('settings')->pluck('value', 'key')->toArray();
        $defaults = [
            'market_name' => 'سوپەرمارکێت',
            'phone' => '',
            'address' => '',
            'receipt_header' => 'ڕاپۆرتی کۆتایی شیفت (Z-REPORT)',
            'receipt_footer' => 'سوپاس بۆ خزمەتکردنتان',
            'currency_symbol' => 'د.ع',
        ];
        $settings = array_merge($defaults, $settingsRaw);

        // ٢. وەسڵەکانی پەیوەست بەم شیفتە
        $orders = Order::where('register_shift_id', $shift->id)
            ->where('status', 'completed')
            ->get();

        $totalCashSales = (float) $orders->where('payment_method', 'cash')->sum('grand_total');
        $totalCreditSales = (float) $orders->where('payment_method', 'debt')->sum('grand_total');
        $totalCardSales = (float) $orders->whereIn('payment_method', ['online', 'card'])->sum('grand_total');
        $totalCodSales = (float) $orders->where('payment_method', 'cod')->sum('grand_total');
        $totalDiscountsGiven = (float) $orders->sum('discount_amount');

        return view('admin.reports.z_report', [
            'shift' => $shift,
            'orders' => $orders,
            'totalCashSales' => $totalCashSales,
            'totalCreditSales' => $totalCreditSales,
            'totalCardSales' => $totalCardSales,
            'totalCodSales' => $totalCodSales,
            'totalDiscountsGiven' => $totalDiscountsGiven,
            'settings' => $settings,
            'marketName' => $settings['market_name'],
            'receiptFooter' => $settings['receipt_footer'],
            'currencySymbol' => $settings['currency_symbol'],
        ]);
    }
}
