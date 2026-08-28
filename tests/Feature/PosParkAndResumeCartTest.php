<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Inventory\Actions\CreateProductAction;
use App\Domains\Inventory\Models\Category;
use App\Domains\Inventory\Models\Unit;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Register;
use App\Domains\Organization\Models\Store;
use App\Domains\POS\Actions\ParkCartAction;
use App\Domains\POS\Actions\ResumeCartAction;
use App\Domains\POS\DTOs\CartItemData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosParkAndResumeCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_park_cart_and_resume_successfully(): void
    {
        // ١. ئامادەکردنی بنکە و سندوق
        $company = Company::create(['name' => 'مارکێتی کوردی']);
        $store = Store::create(['company_id' => $company->id, 'name' => 'فرۆشگای ناوەند', 'code' => 'ST01']);
        $register = Register::create(['store_id' => $store->id, 'name' => 'سندوقی ١', 'code' => 'REG01']);
        $user = User::create([
            'name' => 'کاشێر',
            'username' => 'cashier01',
            'email' => 'cashier@market.com',
            'password' => bcrypt('password123'),
        ]);

        $category = Category::create(['name' => 'شیرەمەنی', 'code' => 'CAT01']);
        $unit = Unit::create(['name' => 'دانە', 'short_code' => 'pcs']);

        $createProductAction = app(CreateProductAction::class);
        $product = $createProductAction->execute([
            'name' => 'ماست',
            'sku' => 'YGT-001',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'retail_price' => 1500,
        ], [
            ['code' => '869000000004', 'type' => 'unit', 'packing_qty' => 1]
        ]);

        // ٢. پارککردنی عەرەبانە بە ٢ دانە ماست
        $cartItem = new CartItemData(
            productId: $product->id,
            quantity: 2.0,
            unitPrice: 1500.0,
            barcode: '869000000004'
        );

        $parkCartAction = app(ParkCartAction::class);
        $suspendedOrder = $parkCartAction->execute($register->id, $user->id, [$cartItem]);

        $this->assertDatabaseHas('suspended_orders', [
            'id' => $suspendedOrder->id,
            'register_id' => $register->id,
            'user_id' => $user->id,
        ]);

        // ٣. گەڕاندنەوەی عەرەبانەکە (Resume Cart)
        $resumeCartAction = app(ResumeCartAction::class);
        $resumedItems = $resumeCartAction->execute($suspendedOrder->id);

        // ٤. پشکنینی ڕاستیی داتاکانی گەڕاوە
        $this->assertCount(1, $resumedItems);
        $this->assertInstanceOf(CartItemData::class, $resumedItems[0]);
        $this->assertEquals($product->id, $resumedItems[0]->productId);
        $this->assertEquals(2.0, $resumedItems[0]->quantity);
        $this->assertEquals(3000.0, $resumedItems[0]->getTotal());

        // دڵنیابوونەوە لە سڕینەوەی لە هەڵپەسێردراوەکان
        $this->assertDatabaseMissing('suspended_orders', [
            'id' => $suspendedOrder->id,
        ]);
    }
}
