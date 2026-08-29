<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function zReport(Request $request, ?string $shiftId = null): View
    {
        $settingsRaw = DB::table('settings')->pluck('value', 'key')->toArray();
        $defaults = [
            'market_name' => 'سوپەرمارکێتی میلاد',
            'market_phone' => '07700000000',
            'market_address' => 'سلێمانی',
            'currency_symbol' => 'د.ع',
        ];
        $settings = array_merge($defaults, $settingsRaw);

        // هێنانی سەرجەم شیفتەکان بۆ لیستی هەڵبژاردن
        $allShifts = DB::table('register_shifts')
            ->leftJoin('users', 'users.id', '=', 'register_shifts.user_id')
            ->leftJoin('registers', 'registers.id', '=', 'register_shifts.register_id')
            ->select('register_shifts.*', 'users.name as cashier_name', 'registers.name as register_name')
            ->orderByDesc('register_shifts.created_at')
            ->get();

        // دیاریکردنی شیفتی مەبەست (یان ئەوەی داواکراوە یان دواین شیفت)
        if ($shiftId) {
            $shift = DB::table('register_shifts')->where('id', $shiftId)->first();
        } else {
            $shift = DB::table('register_shifts')->orderByDesc('created_at')->first();
        }

        $summary = [
            'total_orders' => 0,
            'subtotal' => 0.0,
            'discount_total' => 0.0,
            'tax_total' => 0.0,
            'grand_total' => 0.0,
            'cash_sales' => 0.0,
            'card_sales' => 0.0,
            'debt_sales' => 0.0,
            'total_items_sold' => 0,
        ];

        $cashier = null;
        $register = null;

        if ($shift) {
            $cashier = DB::table('users')->where('id', $shift->user_id)->first();
            $register = DB::table('registers')->where('id', $shift->register_id)->first();

            // دۆزینەوەی فەرمانەکانی ئەم شیفتە لەڕێگەی register_shift_id
            $orders = DB::table('orders')
                ->where('register_shift_id', $shift->id)
                ->get();

            $summary['total_orders'] = $orders->count();
            $summary['subtotal'] = (float) $orders->sum('subtotal');
            $summary['discount_total'] = (float) $orders->sum('discount_amount');
            $summary['tax_total'] = (float) $orders->sum('tax_amount');
            $summary['grand_total'] = (float) $orders->sum('grand_total');

            // دۆزینەوەی بڕی کاڵا فرۆشراوەکان
            $orderIds = $orders->pluck('id')->toArray();
            if (!empty($orderIds)) {
                $summary['total_items_sold'] = (int) DB::table('order_items')
                    ->whereIn('order_id', $orderIds)
                    ->sum('quantity');
            }
        }

        return view('admin.reports.z_report', compact(
            'shift',
            'allShifts',
            'summary',
            'cashier',
            'register',
            'settings'
        ));
    }
}
