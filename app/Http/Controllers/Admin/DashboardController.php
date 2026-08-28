<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\CRM\Models\Party;
use App\Domains\Finance\Models\Expense;
use App\Domains\Inventory\Models\Product;
use App\Domains\POS\Models\Order;
use App\Domains\POS\Models\OrderItem;
use App\Domains\POS\Models\Promotion;
use App\Domains\POS\Models\RegisterShift;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->query('start_date', Carbon::today()->startOfDay()->toDateTimeString());
        $endDate = $request->query('end_date', Carbon::today()->endOfDay()->toDateTimeString());

        // ١. کۆی فرۆش و ژمارەی پسوولەکان
        $totalSales = (float) Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('grand_total');

        $totalOrdersCount = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        // ٢. تێچووی کۆگا و خەرجییەکان و قازانج
        $totalCost = 0.0;
        if (Schema::hasTable('journal_items')) {
            $totalCost = (float) DB::table('journal_items')
                ->join('accounts', 'accounts.id', '=', 'journal_items.account_id')
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_items.journal_entry_id')
                ->where('accounts.code', '5010')
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->sum('journal_items.debit');
        }

        // خەرجییەکانی ئەمڕۆ
        $todayExpenses = 0.0;
        if (Schema::hasTable('expenses')) {
            $todayExpenses = (float) Expense::whereDate('expense_date', Carbon::today())->sum('amount');
        }

        $grossProfit = (float) ($totalSales - $totalCost);
        $netProfit = (float) ($grossProfit - $todayExpenses);
        $profitMargin = $totalSales > 0 ? round(($grossProfit / $totalSales) * 100, 1) : 0;

        // ٣. قەرزەکان
        $customerDebt = 0.0;
        $supplierDebt = 0.0;
        if (Schema::hasTable('parties')) {
            $customerDebt = (float) Party::whereIn('type', ['customer', 'both'])->sum('current_balance');
            $supplierDebt = (float) Party::whereIn('type', ['supplier', 'both'])->sum('current_balance');
        }

        // ٤. شیفتەکانی سندوق
        $shifts = RegisterShift::with(['user', 'register'])
            ->latest('opened_at')
            ->take(6)
            ->get();

        // ٥. دوایین پسوولەکان
        $recentOrders = Order::with(['user', 'register'])
            ->latest()
            ->take(8)
            ->get();

        // ٦. کاڵا پڕفرۆشەکان
        $topProducts = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'completed')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.total_price) as total_sales')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $totalProductsCount = Product::count();
        $activePromosCount = Schema::hasTable('promotions') ? Promotion::where('is_active', true)->count() : 0;

        return view('admin.dashboard', compact(
            'totalSales',
            'totalOrdersCount',
            'grossProfit',
            'netProfit',
            'todayExpenses',
            'profitMargin',
            'customerDebt',
            'supplierDebt',
            'shifts',
            'recentOrders',
            'topProducts',
            'totalProductsCount',
            'activePromosCount',
            'startDate',
            'endDate'
        ));
    }
}
