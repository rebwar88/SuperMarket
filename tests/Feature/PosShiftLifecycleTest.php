<?php

namespace Tests\Feature;

use App\Domains\Auth\Models\User;
use App\Domains\Organization\Models\Company;
use App\Domains\Organization\Models\Register;
use App\Domains\Organization\Models\Store;
use App\Domains\POS\Actions\CloseShiftAction;
use App\Domains\POS\Actions\OpenShiftAction;
use App\Domains\POS\DTOs\ShiftData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosShiftLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_and_close_shift_with_cash_difference_calculation(): void
    {
        // ١. ئامادەکردنی بنکە و سندوق
        $company = Company::create(['name' => 'مارکێتی کوردی']);
        $store = Store::create([
            'company_id' => $company->id,
            'name' => 'فرۆشگای ناوەند',
            'code' => 'ST01',
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

        // ٢. کردنەوەی شیفت بە 50,000 دیناری سەرەتایی
        $openShiftAction = app(OpenShiftAction::class);
        $shiftData = ShiftData::fromArray([
            'register_id' => $register->id,
            'user_id' => $user->id,
            'opening_cash' => 50000.00,
        ]);

        $shift = $openShiftAction->execute($shiftData);

        $this->assertDatabaseHas('register_shifts', [
            'id' => $shift->id,
            'register_id' => $register->id,
            'opening_cash' => 50000.00,
            'status' => 'open',
        ]);

        // ٣. داخستنی شیفت بە 48,000 دینار (واتە ٢٠٠٠ دینار کورتهێنانی هەیە)
        $closeShiftAction = app(CloseShiftAction::class);
        $closeData = ShiftData::fromArray([
            'register_id' => $register->id,
            'user_id' => $user->id,
            'closing_cash' => 48000.00,
        ]);

        $closedShift = $closeShiftAction->execute($shift->id, $closeData);

        // ٤. پشکنینی داخستن و جیاوازیی کاش
        $this->assertEquals('closed', $closedShift->status);
        $this->assertEquals(-2000.00, $closedShift->cash_difference);

        $this->assertDatabaseHas('register_shifts', [
            'id' => $shift->id,
            'closing_cash' => 48000.00,
            'cash_difference' => -2000.00,
            'status' => 'closed',
        ]);
    }
}
