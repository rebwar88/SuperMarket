<?php

namespace Tests\Feature;

use App\Domains\Finance\Models\Account;
use App\Domains\Finance\Models\JournalEntry;
use App\Domains\Inventory\Actions\CreateProductAction;
use App\Domains\Inventory\Models\Category;
use App\Domains\Inventory\Models\Unit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Store;
use App\Domains\Organization\Models\Warehouse;
use App\Domains\Purchases\Actions\CreatePurchaseOrderAction;
use App\Domains\Purchases\Actions\PaySupplierAction;
use App\Domains\Purchases\Actions\ReceiveGoodsAction;
use App\Domains\Purchases\DTOs\PurchaseOrderData;
use App\Domains\Purchases\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseFlowAndSupplierDebtTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_purchase_grn_stock_addition_and_supplier_debt_settlement(): void
    {
        // ١. ئامادەکردنی داتا سەرەتایییەکان
        $company = Company::create(['name' => 'مارکێتی کوردی']);
        $store = Store::create(['company_id' => $company->id, 'name' => 'فرۆشگای ناوەند', 'code' => 'ST01']);
        $warehouse = Warehouse::create(['store_id' => $store->id, 'name' => 'مەخزەنی ناوەند']);

        Account::create(['code' => '1010', 'name' => 'سندوقی کاش', 'type' => 'asset']);
        Account::create(['code' => '1040', 'name' => 'کۆگا و مەخزەن', 'type' => 'asset']);
        Account::create(['code' => '2010', 'name' => 'قەرزی دابینکەران Accounts Payable', 'type' => 'liability']);

        $supplier = Supplier::create([
            'name' => 'کۆمپانیای ئەلبان بۆ بەروبوومی شیرەمەنی',
            'phone' => '07700000000',
            'total_balance' => 0.00,
        ]);

        $category = Category::create(['name' => 'شیرەمەنی', 'code' => 'CAT01']);
        $unit = Unit::create(['name' => 'دانە', 'short_code' => 'pcs']);

        $createProductAction = app(CreateProductAction::class);
        $product = $createProductAction->execute([
            'name' => 'کەرەی کوردی',
            'sku' => 'BTR-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'retail_price' => 2500,
        ], [
            ['code' => '869000000008', 'type' => 'unit', 'packing_qty' => 1]
        ]);

        // ٢. دروستکردنی داواکاری کڕین (PO) بۆ ١٠ دانە بە تێچووی ١٥٠٠ دینار (کۆی گشتی = 15,000)
        $createPoAction = app(CreatePurchaseOrderAction::class);
        $po = $createPoAction->execute(PurchaseOrderData::fromArray([
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'user_id' => '00000000-0000-0000-0000-000000000000',
            'total_amount' => 15000.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10.0,
                    'cost_price' => 1500.0,
                ]
            ]
        ]));

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => 'ordered',
            'total_amount' => 15000.00,
        ]);

        // ٣. وەرگرتنی بار بۆ مەخزەن (GRN)
        $receiveGoodsAction = app(ReceiveGoodsAction::class);
        $grn = $receiveGoodsAction->execute($po->id, $warehouse->id, [
            [
                'product_id' => $product->id,
                'quantity' => 10.0,
                'cost_price' => 1500.0,
                'batch_number' => 'BATCH-BTR-01',
                'expiry_date' => now()->addMonths(6)->toDateString(),
            ]
        ]);

        // پشکنینی هاتنی ستۆک بۆ خشتەی وەجبەکان (Batches)
        $this->assertDatabaseHas('batches', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'stock_qty' => 10.0,
            'purchase_cost' => 1500.0,
        ]);

        // پشکنینی بەرزبوونەوەی قەرزی دابینکەر بۆ 15,000 دینار
        $supplier->refresh();
        $this->assertEquals(15000.00, (float) $supplier->total_balance);

        // پشکنینی تۆماری دەفتەری حیسابی دابینکەر (Ledger)
        $this->assertDatabaseHas('supplier_ledger', [
            'supplier_id' => $supplier->id,
            'entry_type' => 'invoice',
            'amount' => 15000.00,
            'running_balance' => 15000.00,
        ]);

        // پشکنینی قەیدی دارایی ژمێریاری GRN
        $grnJournal = JournalEntry::where('purchase_order_id', $po->id)->with('lines')->first();
        $this->assertNotNull($grnJournal);
        $this->assertEquals(15000.00, $grnJournal->lines->sum('debit'));
        $this->assertEquals(15000.00, $grnJournal->lines->sum('credit'));

        // ٤. پارەدان بە کۆمپانیا بە بڕی ١٠،٠٠٠ دینار
        $paySupplierAction = app(PaySupplierAction::class);
        $paySupplierAction->execute(
            supplierId: $supplier->id,
            amount: 10000.00,
            paymentMethod: 'cash'
        );

        // پشکنینی کەمبوونەوەی قەرز بۆ 5,000 دینار
        $supplier->refresh();
        $this->assertEquals(5000.00, (float) $supplier->total_balance);

        $this->assertDatabaseHas('supplier_ledger', [
            'supplier_id' => $supplier->id,
            'entry_type' => 'payment',
            'amount' => 10000.00,
            'running_balance' => 5000.00,
        ]);
    }
}
