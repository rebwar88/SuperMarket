<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Auth\Models\User;
use App\Domains\Finance\Models\Account;
use App\Domains\Inventory\Models\Unit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Register;
use App\Domains\Organization\Models\Store;
use App\Domains\Organization\Models\Warehouse;
use App\Domains\Settings\Models\CurrencyRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ١. دروستکردنی دەسەڵات و ڕۆڵەکان (Roles & Permissions)

$permissions = [
    'pos.access',
    'pos.void_item',
    'pos.apply_discount',
    'pos.manager_override',
    'inventory.manage',
    'purchases.manage',
    'finance.view_reports',
    'settings.manage',
];

foreach ($permissions as $permission) {
    Permission::findOrCreate($permission, 'web');
}

$adminRole = Role::findOrCreate('admin', 'web');
$adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

$cashierRole = Role::findOrCreate('cashier', 'web');
$cashierRole->syncPermissions(['pos.access']);

$managerRole = Role::findOrCreate('manager', 'web');
$managerRole->syncPermissions(['pos.access', 'pos.void_item', 'pos.apply_discount', 'pos.manager_override', 'inventory.manage']);

        // ٢. پلانی ژمێریاری (Chart of Accounts)
        $accounts = [
            ['code' => '1010', 'name' => 'Cash on Hand (Drawer)', 'type' => 'asset'],
            ['code' => '1020', 'name' => 'Bank Account', 'type' => 'asset'],
            ['code' => '1030', 'name' => 'Accounts Receivable (Customer Debt)', 'type' => 'asset'],
            ['code' => '1040', 'name' => 'Inventory Asset', 'type' => 'asset'],
            ['code' => '2010', 'name' => 'Accounts Payable (Suppliers)', 'type' => 'liability'],
            ['code' => '4010', 'name' => 'Sales Revenue', 'type' => 'revenue'],
            ['code' => '5010', 'name' => 'Cost of Goods Sold (COGS)', 'type' => 'expense'],
            ['code' => '5020', 'name' => 'Inventory Wastage Expense', 'type' => 'expense'],
            ['code' => '6010', 'name' => 'General Store Expenses', 'type' => 'expense'],
        ];

        foreach ($accounts as $acc) {
            Account::firstOrCreate(['code' => $acc['code']], $acc);
        }

        // ٣. یەکەکانی پێوانە (Units)
        $units = [
            ['name' => 'Piece', 'short_code' => 'pcs', 'allow_decimal' => false],
            ['name' => 'Kilogram', 'short_code' => 'kg', 'allow_decimal' => true],
            ['name' => 'Gram', 'short_code' => 'g', 'allow_decimal' => true],
            ['name' => 'Liter', 'short_code' => 'ltr', 'allow_decimal' => true],
            ['name' => 'Pack / Box', 'short_code' => 'pack', 'allow_decimal' => false],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['short_code' => $unit['short_code']], $unit);
        }

        // ٤. کۆمپانیا، لق، مەخزەن و کاشێر (Organization)
        $company = Company::firstOrCreate(
            ['name' => 'SuperMarket HQ'],
            ['tax_number' => 'TAX-2026-IQ', 'phone' => '07700000000', 'email' => 'info@market.local']
        );

        $store = Store::firstOrCreate(
            ['code' => 'STR-01'],
            [
                'company_id' => $company->id,
                'name' => 'Main Branch',
                'receipt_header' => 'Welcome to SuperMarket HQ',
                'receipt_footer' => 'Thank you for shopping with us!',
                'status' => 'active',
            ]
        );

        $warehouse = Warehouse::firstOrCreate(
            ['name' => 'Retail Floor Warehouse', 'store_id' => $store->id],
            ['type' => 'retail']
        );

        $register = Register::firstOrCreate(
            ['code' => 'REG-01'],
            [
                'store_id' => $store->id,
                'name' => 'Lane 1 (Main Cashier)',
                'status' => 'active',
            ]
        );

        // ٥. بەکارهێنەر و ئەدمین (Admin & Cashier Users)
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@market.local',
                'phone' => '07701234567',
                'password' => Hash::make('password123'),
                'pin_code' => '123456',
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');
        $admin->stores()->syncWithoutDetaching([$store->id => ['role' => 'admin']]);

        $cashier = User::firstOrCreate(
            ['username' => 'cashier1'],
            [
                'name' => 'Cashier One',
                'email' => 'cashier1@market.local',
                'phone' => '07709876543',
                'password' => Hash::make('password123'),
                'pin_code' => '112233',
                'is_active' => true,
            ]
        );
        $cashier->assignRole('cashier');
        $cashier->stores()->syncWithoutDetaching([$store->id => ['role' => 'cashier']]);

        // ٦. نرخی ئاڵوگۆڕی دراو (USD to IQD)
        CurrencyRate::firstOrCreate(
            ['currency_code' => 'USD', 'effective_date' => now()->toDateString()],
            ['rate' => 1500.0000]
        );
    }
}