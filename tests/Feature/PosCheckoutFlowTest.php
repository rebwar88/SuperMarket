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
use App\Events\OrderCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PosCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_pos_checkout_flow_and_inventory_deduction(): void
    {
        Event::fake([OrderCompleted::class]);

        // ١. ئامادەکردنی ڕێکخراوە و سندوق
        $company = Company::create(['name' => 'مارکێتی کوردی']);
        $store = Store::create([
            'company_id' => $company->id,
            'name' => 'فرۆشگای ناوەند',
            'code' => 'ST01',
        ]);
        $warehouse = Warehouse::create([
            'store_id' => $store->id,
            'name' => 'مەخزەنی ناوەند',
        ]);
        $register = Register::create([
            'store_id' => $store->id,
            'name' => 'سندوقی ١',
            'code' => 'REG01',
        ]);
        $user = User::create([
            'name' => 'کاشێر',
            'username' => 'cashier01',
            'email' => 'cashier@market.com',
            'password' => bcrypt('password123'),
        ]);

        // کردنەوەی شیفتی سندوق
        $shift = RegisterShift::create([
            'register_id' => $register->id,
            'user_id' => $user->id,
            'opening_cash' => 50000.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        // ٢. دروستکردنی حساباتی دارایی بۆ دەبل ئینتری
        Account::create(['code' => '1010', 'name' => 'سندوقی کاش', 'type' => 'asset']);
        Account::create(['code' => '4010', 'name' => 'داهاتی فرۆشتن', 'type' => 'revenue']);
        Account::create(['code' => '5010', 'name' => 'تێچووی کاڵای فرۆشراو COGS', 'type' => 'expense']);
        Account::create(['code' => '1040', 'name' => 'کۆگا و مەخزەن', 'type' => 'asset']);

        $category = Category::create(['name' => 'خواردەمەنی', 'code' => 'CAT01']);
        $unit = Unit::create(['name' => 'دانە', 'short_code' => 'pcs']);

        // ٣. دروستکردنی کاڵا و دانانی بارکۆد
        $createProductAction = app(CreateProductAction::class);
        $product = $createProductAction->execute([
            'name' => 'شیر کالیبۆ',
            'sku' => 'MLK-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'retail_price' => 1500,
        ], [
            ['code' => '869000000001', 'type' => 'unit', 'packing_qty' => 1]
        ]);

        // ٤. زیادکردنی وەجبەی ستۆک (Batch) بە نرخی کڕینی 1000
        $addStockAction = app(AddStockAction::class);
        $batch = $addStockAction->execute(
            productId: $product->id,
            warehouseId: $warehouse->id,
            quantity: 10.0,
            purchaseCost: 1000.0,
            batchNumber: 'BATCH-001',
            expiryDate: now()->addMonths(6)->toDateString()
        );

        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'stock_qty' => 10.0,
        ]);

        // ٥. ئەنجامدانی فرۆشتن لە سندوق (Checkout) بۆ ٢ دانە
        $checkoutAction = app(ProcessCheckoutAction::class);
        $checkoutData = CheckoutData::fromArray([
            'store_id' => $store->id,
            'register_id' => $register->id,
            'register_shift_id' => $shift->id,
            'user_id' => $user->id,
            'subtotal' => 3000.0,
            'discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'grand_total' => 3000.0,
            'paid_amount' => 5000.0,
            'change_due' => 2000.0,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'quantity' => 2.0,
                    'unit_price' => 1500.0,
                    'total_price' => 3000.0,
                ]
            ]
        ]);

        $order = $checkoutAction->execute($checkoutData);

        // ٦. پشکنینی ڕاستیی داتاکان لە داتابەیس
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'grand_total' => 3000.0,
            'paid_amount' => 5000.0,
            'change_due' => 2000.0,
        ]);

        // کەمبوونی ستۆک بەپێی بنەمای FIFO لە ١٠ دانەوە بۆ ٨ دانە
        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'stock_qty' => 8.0,
        ]);

        // ناردنی ڕووداوی تەواوبوونی فرۆشتن بۆ چاپ
        Event::assertDispatched(OrderCompleted::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }
}
