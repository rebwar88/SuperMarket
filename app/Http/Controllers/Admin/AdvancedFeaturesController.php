<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Inventory\Models\Product;
use App\Domains\POS\Models\Order;
use App\Domains\POS\Models\RegisterShift;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdvancedFeaturesController extends Controller
{
    public function settings(): View
    {
        $settingsRaw = DB::table('settings')->pluck('value', 'key')->toArray();

        $defaults = [
            'market_name' => 'سوپەرمارکێتی میلاد',
            'market_slogan' => 'باشترین کواڵیتی و گونجاوترین نرخ',
            'market_logo' => '',
            'phone' => '07700065765',
            'address' => 'سلێمانی - بەرزاییەکانی سلێمانی',
            'receipt_header' => 'بەخێربێن بۆ سوپەرمارکێت',
            'receipt_footer' => 'سوپاس بۆ کڕینەکەتان',
            'allow_pay_later' => '1',
            'allow_online_pay' => '1',
            'currency_symbol' => 'د.ع',
            'usd_exchange_rate' => '150000',
            'low_stock_alert' => '5',
            'timezone' => 'Asia/Baghdad',
        ];

        $settings = array_merge($defaults, $settingsRaw);

        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->except(['_token', 'market_logo_file']);

        if ($request->hasFile('market_logo_file') && $request->file('market_logo_file')->isValid()) {
            $file = $request->file('market_logo_file');
            $mime = $file->getMimeType();
            $base64 = base64_encode(file_get_contents($file->getRealPath()));
            $data['market_logo'] = "data:{$mime};base64,{$base64}";
        }

        $checkboxes = ['allow_pay_later', 'allow_online_pay'];
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

    public function expenses(): View
    {
        $this->ensureExpenseTablesExist();

        $settingsRaw = DB::table('settings')->pluck('value', 'key')->toArray();
        $defaults = [
            'market_name' => 'سوپەرمارکێت',
            'currency_symbol' => 'د.ع',
        ];
        $settings = array_merge($defaults, $settingsRaw);

        $now = Carbon::now();
        $totalExpensesThisMonth = (float) DB::table('expenses')
            ->whereYear('expense_date', $now->year)
            ->whereMonth('expense_date', $now->month)
            ->sum('amount');

        $totalExpensesToday = (float) DB::table('expenses')
            ->whereDate('expense_date', Carbon::today())
            ->sum('amount');

        $hasCategoryCol = Schema::hasColumn('expenses', 'category_id');

        $query = DB::table('expenses');
        if ($hasCategoryCol) {
            $query->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.category_id')
                  ->select('expenses.*', 'expense_categories.name as category_name');
        } else {
            $query->select('expenses.*', DB::raw("'گشتی' as category_name"));
        }

        $expenses = $query->orderByDesc('expenses.expense_date')
            ->orderByDesc('expenses.created_at')
            ->paginate(15);

        $categories = Schema::hasTable('expense_categories') ? DB::table('expense_categories')->get() : collect();

        return view('admin.expenses.index', compact(
            'expenses',
            'categories',
            'totalExpensesThisMonth',
            'totalExpensesToday',
            'settings'
        ));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $this->ensureExpenseTablesExist();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'category_id' => ['nullable', 'string'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $data = [
            'id' => (string) Str::uuid(),
            'title' => $validated['title'],
            'amount' => (float) $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'notes' => $validated['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('expenses', 'category_id')) {
            $data['category_id'] = $validated['category_id'] ?: null;
        }

        DB::table('expenses')->insert($data);

        return redirect()->route('admin.expenses.index')->with('success', 'خەرجی بە سەرکەوتوویی تۆمارکرا.');
    }

    private function ensureExpenseTablesExist(): void
    {
        if (!Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function ($table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->timestamps();
            });

            DB::table('expense_categories')->insert([
                ['id' => (string) Str::uuid(), 'name' => 'کرێی دوکان', 'created_at' => now(), 'updated_at' => now()],
                ['id' => (string) Str::uuid(), 'name' => 'کارەبا و مۆلیدە', 'created_at' => now(), 'updated_at' => now()],
                ['id' => (string) Str::uuid(), 'name' => 'مووچەی کارمەندان', 'created_at' => now(), 'updated_at' => now()],
                ['id' => (string) Str::uuid(), 'name' => 'خواردن و چایخانە', 'created_at' => now(), 'updated_at' => now()],
                ['id' => (string) Str::uuid(), 'name' => 'کەلوپەل و پاککەرەوە', 'created_at' => now(), 'updated_at' => now()],
                ['id' => (string) Str::uuid(), 'name' => 'هیتر', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function ($table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->decimal('amount', 15, 2);
                $table->uuid('category_id')->nullable();
                $table->date('expense_date');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('expenses', 'category_id')) {
                Schema::table('expenses', function ($table) {
                    $table->uuid('category_id')->nullable();
                });
            }
        }
    }

    public function promotions(): View
    {
        $this->ensurePromotionTablesExist();

        $settingsRaw = DB::table('settings')->pluck('value', 'key')->toArray();
        $defaults = [
            'market_name' => 'سوپەرمارکێت',
            'currency_symbol' => 'د.ع',
        ];
        $settings = array_merge($defaults, $settingsRaw);

        $hasProductCol = Schema::hasColumn('promotions', 'product_id');

        $query = DB::table('promotions');
        if ($hasProductCol) {
            $query->leftJoin('products', 'products.id', '=', 'promotions.product_id')
                  ->select('promotions.*', 'products.name as product_name');
        } else {
            $query->select('promotions.*', DB::raw("NULL as product_name"));
        }

        $promotions = $query->orderByDesc('promotions.created_at')->paginate(15);

        $products = class_exists(Product::class) && Schema::hasTable('products') 
            ? Product::all() 
            : (Schema::hasTable('products') ? DB::table('products')->get() : collect());

        return view('admin.promotions.index', compact('promotions', 'products', 'settings'));
    }

    public function storePromotion(Request $request): RedirectResponse
    {
        $this->ensurePromotionTablesExist();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:percentage,fixed_price,buy_x_get_y'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'product_id' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $data = [
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'discount_value' => (float) $validated['discount_value'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('promotions', 'product_id')) {
            $data['product_id'] = $validated['product_id'] ?: null;
        }

        DB::table('promotions')->insert($data);

        return redirect()->route('admin.promotions.index')->with('success', 'ئۆفەر بە سەرکەوتوویی تۆمارکرا.');
    }

    public function togglePromotion(string $id): RedirectResponse
    {
        $promo = DB::table('promotions')->where('id', $id)->first();
        if ($promo) {
            DB::table('promotions')->where('id', $id)->update([
                'is_active' => !$promo->is_active,
                'updated_at' => now(),
            ]);
        }
        return redirect()->route('admin.promotions.index')->with('success', 'دۆخی ئۆفەرەکە گۆڕدرا.');
    }

    private function ensurePromotionTablesExist(): void
    {
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function ($table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('type')->default('percentage');
                $table->decimal('discount_value', 15, 2)->default(0);
                $table->uuid('product_id')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } else {
            if (!Schema::hasColumn('promotions', 'product_id')) {
                Schema::table('promotions', function ($table) {
                    $table->uuid('product_id')->nullable();
                });
            }
        }
    }

    public function zReport(string $shiftId): View
    {
        $shift = RegisterShift::with(['user', 'register'])->find($shiftId);

        if (!$shift) {
            $shiftRow = DB::table('register_shifts')->where('id', $shiftId)->first();
            if (!$shiftRow) abort(404, 'شیفت نەدۆزرایەوە');
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

        $settingsRaw = DB::table('settings')->pluck('value', 'key')->toArray();
        $defaults = [
            'market_name' => 'سوپەرمارکێتی میلاد',
            'market_logo' => '',
            'phone' => '',
            'address' => '',
            'receipt_header' => 'ڕاپۆرتی کۆتایی شیفت (Z-REPORT)',
            'receipt_footer' => 'سوپاس بۆ کڕینەکەتان',
            'currency_symbol' => 'د.ع',
            'timezone' => 'Asia/Baghdad',
        ];
        $settings = array_merge($defaults, $settingsRaw);

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
}
