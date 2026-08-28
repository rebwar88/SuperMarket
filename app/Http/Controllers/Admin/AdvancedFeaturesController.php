<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Finance\Models\Expense;
use App\Domains\Inventory\Models\Product;
use App\Domains\Organization\Models\Setting;
use App\Domains\POS\Models\Order;
use App\Domains\POS\Models\Promotion;
use App\Domains\POS\Models\RegisterShift;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdvancedFeaturesController extends Controller
{
    public function __construct()
    {
        $this->ensureTables();
    }

    // --- بەشی خەرجییەکان (Expenses) ---
    public function expenses(): View
    {
        $expenses = Expense::with('user')->latest('expense_date')->paginate(15);
        $totalExpensesThisMonth = Expense::whereMonth('expense_date', Carbon::now()->month)->sum('amount');

        return view('admin.expenses.index', compact('expenses', 'totalExpensesThisMonth'));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        Expense::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'amount' => (float) $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes' => $validated['notes'] ?? null,
            'user_id' => $request->user()?->id,
        ]);

        return redirect()->route('admin.expenses.index')->with('success', 'خەرجی بە سەرکەوتوویی تۆمارکرا.');
    }

    // --- بەشی داشکاندن و ئۆفەرەکان (Promotions) ---
    public function promotions(): View
    {
        $promotions = Promotion::with('product')->latest()->get();
        $products = Product::all();

        return view('admin.promotions.index', compact('promotions', 'products'));
    }

    public function storePromotion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed_discount,bogo'],
            'product_id' => ['required', 'string', 'exists:products,id'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'buy_quantity' => ['nullable', 'numeric', 'min:1'],
            'get_quantity' => ['nullable', 'numeric', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        Promotion::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'product_id' => $validated['product_id'],
            'discount_value' => (float) ($validated['discount_value'] ?? 0),
            'buy_quantity' => (float) ($validated['buy_quantity'] ?? 1),
            'get_quantity' => (float) ($validated['get_quantity'] ?? 0),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.promotions.index')->with('success', 'ئۆفەرەکە بە سەرکەوتوویی زیادکرا.');
    }

    // --- ڕاپۆرتی کۆتایی ڕۆژ (Z-Report) ---
    public function zReport(string $shiftId): View
    {
        $shift = RegisterShift::with(['user', 'register'])->findOrFail($shiftId);
        
        $orders = Order::where('register_shift_id', $shift->id)
            ->where('status', 'completed')
            ->get();

        $totalCashSales = $orders->where('payment_method', 'cash')->sum('grand_total');
        $totalCreditSales = $orders->where('payment_method', 'debt')->sum('grand_total');
        $totalCardSales = $orders->where('payment_method', 'card')->sum('grand_total');
        $totalDiscountsGiven = $orders->sum('discount_amount');

        $marketName = Setting::get('market_name', 'سوپەرمارکێتی مۆدێرن');
        $receiptFooter = Setting::get('receipt_footer', 'سوپاس بۆ سەردانەکەتان');

        return view('admin.reports.z_report', compact(
            'shift',
            'orders',
            'totalCashSales',
            'totalCreditSales',
            'totalCardSales',
            'totalDiscountsGiven',
            'marketName',
            'receiptFooter'
        ));
    }

    // --- ڕێکخستنەکانی سیستەم (Settings) ---
    public function settings(): View
    {
        $settings = [
            'market_name' => Setting::get('market_name', 'سوپەرمارکێتی مۆدێرن'),
            'phone' => Setting::get('phone', '0770 000 0000'),
            'address' => Setting::get('address', 'سلێمانی - شەقامی سەرەکی'),
            'receipt_footer' => Setting::get('receipt_footer', 'سوپاس بۆ کڕینەکەتان، کاڵای فرۆشراو دەگۆڕدرێتەوە لە ماوەی ٢٤ کاتژمێردا.'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::set($key, (string) $value);
        }

        return redirect()->route('admin.settings.index')->with('success', 'ڕێکخستنەکان بە سەرکەوتوویی نوێکرانەوە.');
    }

    private function ensureTables(): void
    {
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function ($table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->string('category');
                $table->decimal('amount', 15, 2);
                $table->date('expense_date');
                $table->string('notes')->nullable();
                $table->uuid('user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function ($table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('type');
                $table->uuid('product_id');
                $table->decimal('discount_value', 15, 2)->default(0);
                $table->decimal('buy_quantity', 15, 2)->default(1);
                $table->decimal('get_quantity', 15, 2)->default(0);
                $table->dateTime('start_date');
                $table->dateTime('end_date');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function ($table) {
                $table->string('key')->primary();
                $table->text('value')->nullable();
            });
        }
    }
}
