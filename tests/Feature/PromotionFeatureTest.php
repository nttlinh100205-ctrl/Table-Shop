<?php

namespace Tests\Feature;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionFeatureTest extends TestCase
{
    /**
     * Test logic kiểm tra điều kiện của Promotion model
     */
    public function test_promotion_eligibility_check(): void
    {
        $promo = Promotion::firstOrCreate(
            ['code' => 'TESTPROMO50'],
            [
                'name'                => 'Mã test giảm 50k',
                'description'         => 'Test giảm 50k cho đơn từ 200k',
                'discount_type'       => 'fixed',
                'discount_value'      => 50000,
                'min_order_amount'    => 200000,
                'usage_limit'         => 10,
                'used_count'          => 0,
                'start_date'          => now()->subDay(),
                'end_date'            => now()->addDay(),
                'is_active'           => true,
            ]
        );

        // Trường hợp 1: Đơn 300k (>= 200k) => Hợp lệ
        $infoEligible = $promo->getEligibilityInfo(300000);
        $this->assertTrue($infoEligible['is_eligible']);
        $this->assertEquals(50000, $infoEligible['discount_amount']);
        $this->assertEquals(0, $infoEligible['need_more']);

        // Trường hợp 2: Đơn 150k (< 200k) => Không hợp lệ, cần mua thêm 50k
        $infoIneligible = $promo->getEligibilityInfo(150000);
        $this->assertFalse($infoIneligible['is_eligible']);
        $this->assertEquals(50000, $infoIneligible['need_more']);
        $this->assertNotEmpty($infoIneligible['reason']);
    }

    /**
     * Test API lấy danh sách khuyến mãi /user/promotions
     */
    public function test_promotions_api_returns_available_and_ineligible(): void
    {
        $response = $this->getJson(route('user.promotions.index', ['subtotal' => 250000]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'subtotal',
                'subtotal_text',
                'available',
                'ineligible',
            ]);
    }
}
