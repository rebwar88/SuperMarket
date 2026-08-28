<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Finance\Models\Account;
use App\Domains\Inventory\Actions\AddStockAction;
use App\Domains\Inventory\Actions\CreateProductAction;
use App\Domains\Inventory\Models\Category;
use App\Domains\Inventory\Models\Unit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Register;
use App\Domains\Organization\Models\Store;
use App\Domains\Organization\Models\Warehouse;
use App\Domains\POS\Actions\ProcessCheckoutAction;
use App\Domains\POS\DTOs\CheckoutData;
use App\Domains\POS\Models\RegisterShift;
use App\Domains\Reporting\Actions\GenerateDailySalesReportAction;
use App\Domains\Reporting\Actions\GenerateProfitMarginReportAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingAndProfitabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_profit_margin_and_daily_sales_report_calculation(): void
    {
        // ١. ئامادەکردنی فرۆشگا و داتاکان
        $company = Company::create(['name' => 'مارکێتی کوردی']);
        $store = Store::create(['company_id' => $company->id, 'name' => 'فرۆشگای سەرەکی', 'code' => 'ST01']);
        $warehouse = Warehouse::create(['store_id' => $store->id, 'name' => 'مەخزەن']);
        $register = Register::create(['store_id' => $store->id, 'name' => 'سندوقی ١', 'code' => 'REG01']);
        $user = User::create([
            'name' => 'کاشێر',
            'username' => 'cashier_rep',
            'email' => 'rep@market.com',
            'password' => bcrypt('password123'),
        ]);

        $shift = RegisterShift::create([
            'register_id' => $register->id,
            'user_id' => $user->id,
            'opening_cash' => 10000.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        Account::create(['code' => '1010', 'name' => 'سندوقی کاش', 'type' => 'asset']);
        Account::create(['code' => '4010', 'name' => 'داهاتی فرۆشتن', 'type' => 'revenue']);
        Account::create(['code' => '5010', 'name' => 'تێچووی کاڵای فرۆشراو COGS', 'type' => 'expense']);
        Account::create(['code' => '1040', 'name' => 'کۆگا و مەخزەن', 'type' => 'asset']);

        $category = Category::create(['name' => 'خۆراک', 'code' => 'CAT01']);
        $unit = Unit::create(['name' => 'دانە', 'short_code' => 'pcs']);

        // کاڵا: کڕین = 600 دینار، فرۆشتن = 1000 دینار
        $createProductAction = app(CreateProductAction::class);
        $product = $createProductAction->execute([
            'name' => 'چپسی پەتاتە',
            'sku' => 'CHP-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'retail_price' => 1000.00,
        ], [
            ['code' => '869000000099', 'type' => 'unit', 'packing_qty' => 1]
        ]);

        $addStockAction = app(AddStockAction::class);
        $addStockAction->execute(
            productId: $product->id,
            warehouseId: $warehouse->id,
            quantity: 20.0,
            purchaseCost: 600.00,
            batchNumber: 'BATCH-CHP-01',
            expiryDate: now()->addMonths(4)->toDateString()
        );

        // ٢. فرۆشتنی ٥ دانە
        $checkoutAction = app(ProcessCheckoutAction::class);
        $checkoutData = CheckoutData::fromArray([
            'store_id' => $store->id,
            'register_id' => $register->id,
            'register_shift_id' => $shift->id,
            'user_id' => $user->id,
            'subtotal' => 5000.00,
            'discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'grand_total' => 5000.00,
            'paid_amount' => 5000.00,
            'change_due' => 0.0,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'quantity' => 5.0,
                    'unit_price' => 1000.00,
                    'total_price' => 5000.00,
                ]
            ]
        ]);

        $checkoutAction->execute($checkoutData);

        // ٣. ئەنجامدانی ڕاپۆرتی قازانجی کاڵا (Profit Margin)
        // فرۆش = 5000، تێچوو = 3000 (5 * 600)، قازانج = 2000 => مارجین = 40%
        $generateMarginReport = app(GenerateProfitMarginReportAction::class);
        $snapshots = $generateMarginReport->execute();

        $this->assertCount(1, $snapshots);
        $snapshot = $snapshots->first();

        $this->assertEquals($product->id, $snapshot->product_id);
        $this->assertEquals(5.0, (float) $snapshot->units_sold);
        $this->assertEquals(40.00, (float) $snapshot->margin_percent);

        // ٤. ئەنجامدانی ڕاپۆرتی ڕۆژانەی فرۆشگا (Daily Sales Summary)
        $generateDailySales = app(GenerateDailySalesReportAction::class);
        $dailySummary = $generateDailySales->execute($store->id);

        $this->assertEquals(5000.00, (float) $dailySummary->total_sales);
        $this->assertEquals(2000.00, (float) $dailySummary->total_gross_profit);
        $this->assertEquals(1, $dailySummary->total_transactions);
    }
}
