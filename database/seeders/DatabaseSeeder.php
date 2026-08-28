<?php

namespace Database\Seeders;

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
use App\Domains\POS\Models\RegisterShift;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ١. کۆمپانیا و لقی فرۆشگا
        $company = Company::firstOrCreate(['name' => 'سوپەرمارکێتی کوردی']);
        $store = Store::firstOrCreate(['name' => 'فرۆشگای سەرەکی'], [
            'company_id' => $company->id,
            'code' => 'ST01',
        ]);
        $warehouse = Warehouse::firstOrCreate(['name' => 'مەخزەنی سەرەکی'], [
            'store_id' => $store->id,
        ]);
        $register = Register::firstOrCreate(['code' => 'REG01'], [
            'store_id' => $store->id,
            'name' => 'سندوقی ١',
        ]);

        // ٢. بەکارهێنەر و شیفتی کراوە
        $user = User::firstOrCreate(['email' => 'cashier@market.com'], [
            'name' => 'کاشێری سەرەکی',
            'username' => 'cashier',
            'password' => bcrypt('password123'),
        ]);

        RegisterShift::firstOrCreate(['register_id' => $register->id, 'status' => 'open'], [
            'user_id' => $user->id,
            'opening_cash' => 50000.00,
            'opened_at' => now(),
        ]);

        // ٣. هەژمارە سەرەکییەکانی ژمێریاری
        Account::firstOrCreate(['code' => '1010'], ['name' => 'سندوقی کاش', 'type' => 'asset']);
        Account::firstOrCreate(['code' => '4010'], ['name' => 'داهاتی فرۆشتن', 'type' => 'revenue']);
        Account::firstOrCreate(['code' => '5010'], ['name' => 'تێچووی کاڵای فرۆشراو COGS', 'type' => 'expense']);
        Account::firstOrCreate(['code' => '1040'], ['name' => 'کۆگا و مەخزەن', 'type' => 'asset']);

        // ٤. یەکە و کەرتەکان
        $catFood = Category::firstOrCreate(['code' => 'CAT-FOOD'], ['name' => 'خۆراک و خواردنەوە']);
        $catFruit = Category::firstOrCreate(['code' => 'CAT-FRUIT'], ['name' => 'میوە و سەوزەوات']);

        $unitPcs = Unit::firstOrCreate(['short_code' => 'pcs'], ['name' => 'دانە']);
        $unitKg = Unit::firstOrCreate(['short_code' => 'kg'], ['name' => 'کیلۆگرام']);

        $createProduct = app(CreateProductAction::class);
        $addStock = app(AddStockAction::class);

        // ٥. دروستکردنی کاڵاکان بە بارکۆد و ستۆکەوە
        
        // شیری کالی
        $p1 = $createProduct->execute([
            'name' => 'شیری کالی ١ لیتر',
            'sku' => 'MILK-01',
            'category_id' => $catFood->id,
            'unit_id' => $unitPcs->id,
            'retail_price' => 1500.00,
        ], [
            ['code' => '869000000001', 'type' => 'unit', 'packing_qty' => 1]
        ]);
        $addStock->execute($p1->id, $warehouse->id, 60.0, 1100.00, 'BATCH-MILK-01');

        // زەیتی زەیتوون
        $p2 = $createProduct->execute([
            'name' => 'زەیتی زەیتوون ٥٠٠ مل',
            'sku' => 'OIL-02',
            'category_id' => $catFood->id,
            'unit_id' => $unitPcs->id,
            'retail_price' => 4500.00,
        ], [
            ['code' => '869000000002', 'type' => 'unit', 'packing_qty' => 1]
        ]);
        $addStock->execute($p2->id, $warehouse->id, 40.0, 3500.00, 'BATCH-OIL-01');

        // ماستی خۆماڵی
        $p3 = $createProduct->execute([
            'name' => 'ماستی خۆماڵی ١ کیلۆیی',
            'sku' => 'YOG-03',
            'category_id' => $catFood->id,
            'unit_id' => $unitPcs->id,
            'retail_price' => 2000.00,
        ], [
            ['code' => '869000000003', 'type' => 'unit', 'packing_qty' => 1]
        ]);
        $addStock->execute($p3->id, $warehouse->id, 30.0, 1400.00, 'BATCH-YOG-01');

        // نیسکافێ ٣ لە ١
        $p4 = $createProduct->execute([
            'name' => 'نیسکافێ کلاسیک ٣ لە ١',
            'sku' => 'NES-04',
            'category_id' => $catFood->id,
            'unit_id' => $unitPcs->id,
            'retail_price' => 500.00,
        ], [
            ['code' => '869000000004', 'type' => 'unit', 'packing_qty' => 1]
        ]);
        $addStock->execute($p4->id, $warehouse->id, 150.0, 350.00, 'BATCH-NES-01');

        // سێوی سوور
        $p5 = $createProduct->execute([
            'name' => 'سێوی لوبنانی پلە یەک',
            'sku' => 'APP-05',
            'category_id' => $catFruit->id,
            'unit_id' => $unitKg->id,
            'retail_price' => 1500.00,
        ], [
            ['code' => '210000501500', 'type' => 'weight', 'packing_qty' => 1]
        ]);
        $addStock->execute($p5->id, $warehouse->id, 200.0, 1000.00, 'BATCH-APP-01');
    }
}
