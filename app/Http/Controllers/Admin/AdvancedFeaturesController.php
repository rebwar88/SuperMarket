<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\POS\Models\Order;
use App\Domains\POS\Models\RegisterShift;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdvancedFeaturesController extends Controller
{
    public function settings(): View
    {
        $settingsRaw = DB::table('settings')->pluck('value', 'key')->toArray();

        $defaults = [
            'market_name' => 'سوپەرمارکێتی میلاد',
            'phone' => '07700000000',
            'address' => 'سلێمانی',
            'receipt_header' => 'بەخێربێن بۆ سوپەرمارکێت',
            'receipt_footer' => 'سوپاس بۆ کڕینەکەتان، کاڵای فرۆشراو دەگۆڕدرێتەوە لە ماوەی ٢٤ کاتژمێردا.',
            'allow_pay_later' => '1',
            'allow_online_pay' => '1',
            'auto_print_receipt' => '1',
            'currency_symbol' => 'د.ع',
            'usd_exchange_rate' => '150000',
            'low_stock_alert' => '5',
        ];

        $settings = array_merge($defaults, $settingsRaw);

        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->except(['_token']);

        $checkboxes = ['allow_pay_later', 'allow_online_pay', 'auto_print_receipt'];
        foreach ($checkboxes as $cb) {
            $data[$cb] = $request->has($cb) ? '1' : '0';
        }

        foreach ($data as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => (string) ($value ?? '')]
            );
        }

        return redirect()->route('admin.settings.index')->with('success', 'ڕێکخستنەکان بە سەرکەوتوویی نوێکرانەوە.');
    }

    public function zReport(string $shiftId): View
    {
        // دۆزینەوەی شیفت لە RegisterShift یان لە خشتەی ڕاستەوخۆ بەپێی UUID یان ID
        $shift = RegisterShift::with(['user', 'register'])->find($shiftId);

        if (!$shift) {
            $shiftRow = DB::table('register_shifts')->where('id', $shiftId)->first();
            if (!$shiftRow) {
                abort(404, 'شیفت نەدۆزرایەوە');
            }
            $shift = (object) [
                'id' => $shiftRow->id,
                'user' => (object) ['name' => DB::table('users')->where('id', $shiftRow->user_id)->value('name') ?? 'کاشێر'],
                'register' => (object) ['name' => DB::table('registers')->where('id', $shiftRow->register_id)->value('name') ?? 'REG-01'],
                'opened_at' => $shiftRow->opened_at,
                'closed_at' => $shiftRow->closed_at,
                'opening_cash' => $shiftRow->opening_cash,
                'closing_cash' => $shiftRow->closing_cash,
            ];
        }

        // هێنانی ڕێکخستنەکان
        $settingsRaw = DB::table('settings')->pluck('value', 'key')->toArray();
        $defaults = [
            'market_name' => 'سوپەرمارکێتی میلاد',
            'phone' => '',
            'address' => '',
            'receipt_header' => 'ڕاپۆرتی کۆتایی شیفت (Z-REPORT)',
            'receipt_footer' => 'سوپاس بۆ کڕینەکەتان',
            'currency_symbol' => 'د.ع',
        ];
        $settings = array_merge($defaults, $settingsRaw);

        // هێنانی وەسڵەکان بە register_shift_id یان shift_id
        $orders = Order::where(function($q) use ($shiftId) {
                $q->where('register_shift_id', $shiftId);
                if (Schema::hasColumn('orders', 'shift_id')) {
                    $q->orWhere('shift_id', $shiftId);
                }
            })
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

    public function expenses(): View
    {
        return view('admin.expenses.index');
    }

    public function promotions(): View
    {
        return view('admin.promotions.index');
    }
}
