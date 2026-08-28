<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Finance\Models\Account;
use App\Domains\Finance\Models\JournalEntry;
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

class PosMultiBatchFifoAndAccountingTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_batch_fifo_deduction_and_balanced_ledger(): void
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
            'username' => 'cashier02',
            'email' => 'cashier2@market.com',
            'password' => bcrypt('password123'),
        ]);

        $shift = RegisterShift::create([
            'register_id' => $register->id,
            'user_id' => $user->id,
            'opening_cash' => 20000.00,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        // ٢. دروستکردنی حساباتی دارایی
        $cashAccount = Account::create(['code' => '1010', 'name' => 'سندوقی کاش', 'type' => 'asset']);
        $salesAccount = Account::create(['code' => '4010', 'name' => 'داهاتی فرۆشتن', 'type' => 'revenue']);
        $cogsAccount = Account::create(['code' => '5010', 'name' => 'تێچووی کاڵای فرۆشراو COGS', 'type' => 'expense']);
        $inventoryAccount = Account::create(['code' => '1040', 'name' => 'کۆگا و مەخزەن', 'type' => 'asset']);

        $category = Category::create(['name' => 'شیرەمەنی', 'code' => 'CAT02']);
        $unit = Unit::create(['name' => 'دانە', 'short_code' => 'pcs']);

        // ٣. دروستکردنی کاڵا بە نرخی فرۆشتنی 2000
        $createProductAction = app(CreateProductAction::class);
        $product = $createProductAction->execute([
            'name' => 'پەنیر',
            'sku' => 'CHZ-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'retail_price' => 2000,
        ], [
            ['code' => '869000000002', 'type' => 'unit', 'packing_qty' => 1]
        ]);

        // ٤. زیادکردنی دوو وەجبەی جیاواز (وەجبەی یەکەم کۆنترە بەروارەکەی)
        $addStockAction = app(AddStockAction::class);
        
        // وەجبەی ١: ٣ دانە بە نرخی کڕینی 1000 دینار (بەسەرچوون: پاش ٢ مانگ)
        $batch1 = $addStockAction->execute(
            productId: $product->id,
            warehouseId: $warehouse->id,
            quantity: 3.0,
            purchaseCost: 1000.0,
            batchNumber: 'BATCH-EARLY',
            expiryDate: now()->addMonths(2)->toDateString()
        );

        // وەجبەی ٢: ٥ دانە بە نرخی کڕینی 1200 دینار (بەسەرچوون: پاش ٦ مانگ)
        $batch2 = $addStockAction->execute(
            productId: $product->id,
            warehouseId: $warehouse->id,
            quantity: 5.0,
            purchaseCost: 1200.0,
            batchNumber: 'BATCH-LATER',
            expiryDate: now()->addMonths(6)->toDateString()
        );

        // ٥. فرۆشتنی ٤ دانە (دەبێت ٣ دانە لە وەجبەی یەکەم و ١ دانە لە وەجبەی دووەم کەم بکات)
        // کۆی تێچووی کڕین (COGS) = (3 * 1000) + (1 * 1200) = 4200
        // کۆی فرۆشتن = 4 * 2000 = 8000
        $checkoutAction = app(ProcessCheckoutAction::class);
        $checkoutData = CheckoutData::fromArray([
            'store_id' => $store->id,
            'register_id' => $register->id,
            'register_shift_id' => $shift->id,
            'user_id' => $user->id,
            'subtotal' => 8000.0,
            'discount_amount' => 0.0,
            'tax_amount' => 0.0,
            'grand_total' => 8000.0,
            'paid_amount' => 10000.0,
            'change_due' => 2000.0,
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'unit_id' => $unit->id,
                    'quantity' => 4.0,
                    'unit_price' => 2000.0,
                    'total_price' => 8000.0,
                ]
            ]
        ]);

        $order = $checkoutAction->execute($checkoutData);

        // ٦. پشکنینی پاشماوەی ستۆک لە وەجبەکان
        $this->assertDatabaseHas('batches', [
            'id' => $batch1->id,
            'stock_qty' => 0.0, // وەجبەی یەکەم بە تەواوی تەواو بووە
        ]);

        $this->assertDatabaseHas('batches', [
            'id' => $batch2->id,
            'stock_qty' => 4.0, // وەجبەی دووەم ١ دانەی لێ ڕۆیشتووە
        ]);

        // ٧. پشکنینی قەیدی دارایی (Journal Entry) و هاوسەنگی دەبل ئینتری
        $journalEntry = JournalEntry::where('order_id', $order->id)->with('lines')->first();
        $this->assertNotNull($journalEntry);

        $totalDebit = $journalEntry->lines->sum('debit');
        $totalCredit = $journalEntry->lines->sum('credit');

        // پێویستە کۆی دەبیت و کریدیت تەواو یەکسان بن: 8000 (داهات/کاش) + 4200 (تێچوو/مەخزەن) = 12200
        $this->assertEquals(12200.0, $totalDebit);
        $this->assertEquals(12200.0, $totalCredit);
        $this->assertEquals($totalDebit, $totalCredit);
    }
}
