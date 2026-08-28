<?php

namespace Tests\Feature;

use App\Domains\Pricing\Actions\ApplyPromotionsAction;
use App\Domains\Pricing\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosPromotionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_percentage_discount_calculation(): void
    {
        $promotion = Promotion::create([
            'name' => 'داشکاندنی 20 لەسەد',
            'type' => 'percentage',
            'discount_value' => 20.00,
            'is_active' => true,
        ]);

        $action = app(ApplyPromotionsAction::class);
        $result = $action->execute([
            [
                'product_id' => 'prod-1',
                'quantity' => 2,
                'unit_price' => 5000.00, // کۆی گشتی = 10,000
                'promotion_id' => $promotion->id,
            ]
        ]);

        // 20% ی 10,000 دەکاتە 2,000 داشکاندن و نرخی کۆتایی = 8,000
        $this->assertEquals(2000.00, $result['total_discount']);
        $this->assertEquals(8000.00, $result['items'][0]['total_price']);
    }

    public function test_bogo_buy_one_get_one_free_discount_calculation(): void
    {
        $promotion = Promotion::create([
            'name' => 'یەک دانە بکڕە و دانەیەک بە دیاری وەرگرە',
            'type' => 'bogo',
            'discount_value' => 0.00,
            'is_active' => true,
        ]);

        $action = app(ApplyPromotionsAction::class);
        
        // فرۆشتنی 3 دانە بە نرخی 1500 دینار: 1 دانەی بە دیاری بێت دەبێت 1500 داشکێنرێت
        $result = $action->execute([
            [
                'product_id' => 'prod-2',
                'quantity' => 3,
                'unit_price' => 1500.00, // 3 * 1500 = 4500
                'promotion_id' => $promotion->id,
            ]
        ]);

        $this->assertEquals(1500.00, $result['total_discount']);
        $this->assertEquals(3000.00, $result['items'][0]['total_price']);
    }

    public function test_fixed_amount_discount_calculation(): void
    {
        $promotion = Promotion::create([
            'name' => 'داشکاندنی 500 دینار بۆ هەر دانەیەک',
            'type' => 'fixed_discount',
            'discount_value' => 500.00,
            'is_active' => true,
        ]);

        $action = app(ApplyPromotionsAction::class);
        $result = $action->execute([
            [
                'product_id' => 'prod-3',
                'quantity' => 2,
                'unit_price' => 2000.00, // 2 * 2000 = 4000
                'promotion_id' => $promotion->id,
            ]
        ]);

        // داشکاندن: 2 * 500 = 1000 دینار، نرخی کۆتایی = 3000 دینار
        $this->assertEquals(1000.00, $result['total_discount']);
        $this->assertEquals(3000.00, $result['items'][0]['total_price']);
    }
}
