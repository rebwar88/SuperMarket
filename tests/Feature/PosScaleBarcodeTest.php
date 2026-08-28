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
use App\Domains\Pricing\Services\ScaleBarcodeParser;
use App\Events\OrderCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PosScaleBarcodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scale_barcode_parsing_and_decimal_inventory_deduction(): void
    {
        Event::fake([OrderCompleted::class]);

        // ١. ئامادەکردنی بنکە و سندوق
        $company = Company::create(['name' => 'مارکێتی کوردی']);
        $store = Store::create(['company_id' => $company->id, 'name' => 'فرۆشگای ناوەند', 'code' => 'ST01']);
        $warehouse = Warehouse::create(['store_id' => $store->id, 'name' => 'مەخزەنی سەوزەوات']);
        $register = Register::create(['store_id' => $store->id, 'name' => 'سندوقی ١', 'code' => 'REG01']);
        $user = User::create([
            'name' => 'کاشێر',
            'username' => 'cashier_scale',
            'email' => 'cashier_scale@market.com',
            'password' => bcrypt('password123'),
        ]);

        $shift = RegisterShift::create([
            'register_id' => $register->id,
            'user_id' => $user->id,
            'opening_cash' => 25000.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        Account::create(['code' => '1010', 'name' => 'سندوقی کاش', 'type' => 'asset']);
        Account::create(['code' => '4010', 'name' => 'داهاتی فرۆشتن', 'type' => 'revenue']);
        Account::create(['code' => '5010', 'name' => 'تێچووی کاڵای فرۆشراو COGS', 'type' => 'expense']);
        Account::create(['code' => '1040', 'name' => 'کۆگا و مەخزەن', 'type' => 'asset']);

        $category = Category::create(['name' => 'میوە و سەوزە', 'code' => 'VEG']);
        $unitKg = Unit::create(['name' => 'کیلۆگرام', 'short_code' => 'kg', 'allow_decimal' => true]);

        // دروستکردنی کاڵای سێو بە نرخی فرۆشتنی 2000 دینار بۆ هەر کیلۆیەک
        $createProductAction = app(CreateProductAction::class);
        $product = $createProductAction->execute([
            'name' => 'سێوی کوردی',
            'sku' => 'APL-001',
            'category_id' => $category->id,
            'unit_id' => $unitKg->id,
            'retail_price' => 2000.00,
        ], [
            ['code' => '00045', 'type' => 'scale', 'packing_qty' => 1]
        ]);

        // زیادکردنی ١٠ کیلۆگرام ستۆک بە نرخی کڕینی 1200 دینار
        $addStockAction = app(AddStockAction::class);
        $batch = $addStockAction->execute(
            productId: $product->id,
            warehouseId: $warehouse->id,
            quantity: 10.000,
            purchaseCost: 1200.00,
            batchNumber: 'BATCH-APL',
            expiryDate: now()->addDays(15)->toDateString()
        );

        // ٢. پشکنینی شیکردنەوەی بارکۆدی تەرازوو: 1.450 کیلۆگرام
        $barcodeString = '2100045014503';
        $scaleParser = app(ScaleBarcodeParser::class);
        $parsedData = $scaleParser->parse($barcodeString);

        $this->assertEquals('21', $parsedData->prefix);
        $this->assertEquals('00045', $parsedData->itemCode);
        $this->assertEquals(1.450, $parsedData->weightInKg);

        // ٣. فرۆشتنی ١.٤٥٠ کیلۆگرام لە سندوق
        // نرخ = 1.450 * 2000 = 2900 دینار
        $itemTotal = round($parsedData->weightInKg * (float) $product->retail_price, 2);
        $this->assertEquals(2900.00, $itemTotal);

        $checkoutAction = app(ProcessCheckoutAction::class);
        $checkoutData = CheckoutData::fromArray([
            'store_id' => $store->id,
            'register_id' => $register->id,
            'register_shift_id' => $shift->id,
            'user_id' => $user->id,
            'subtotal' => 2900.00,
            'discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'grand_total' => 2900.00,
            'paid_amount' => 5000.00,
            'change_due' => 2100.00,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $unitKg->id,
                    'quantity' => 1.450,
                    'unit_price' => 2000.00,
                    'total_price' => 2900.00,
                ]
            ]
        ]);

        $order = $checkoutAction->execute($checkoutData);

        // ٤. پشکنینی کەمبوونی ستۆکی بەشەکی لە داتابەیس: 10.000 - 1.450 = 8.550
        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'stock_qty' => 8.550,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'grand_total' => 2900.00,
        ]);
    }
}
