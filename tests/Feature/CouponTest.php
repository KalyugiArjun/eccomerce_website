<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_percentage_coupon_with_max_discount_cap(): void
    {
        $user = User::factory()->create();

        // 20% off up to ₹200 on min spend ₹500
        $coupon = Coupon::create([
            'code' => 'SAVE20',
            'discount_type' => 'percentage',
            'discount_amount' => 20,
            'min_order_amount' => 500,
            'max_discount_amount' => 200,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(7),
            'usage_limit' => 100,
            'is_active' => true,
        ]);

        // Spend below min amount -> invalid
        $checkBelow = $coupon->isValidForAmount(400, $user->id);
        $this->assertFalse($checkBelow['valid']);

        // Spend ₹600 -> 20% of 600 is 120 -> valid, discount 120
        $checkNormal = $coupon->isValidForAmount(600, $user->id);
        $this->assertTrue($checkNormal['valid']);
        $this->assertEquals(120, $checkNormal['discount']);

        // Spend ₹2000 -> 20% of 2000 is 400, but capped at 200 -> discount 200
        $checkCapped = $coupon->isValidForAmount(2000, $user->id);
        $this->assertTrue($checkCapped['valid']);
        $this->assertEquals(200, $checkCapped['discount']);
    }

    public function test_fixed_amount_coupon(): void
    {
        $user = User::factory()->create();

        $coupon = Coupon::create([
            'code' => 'FLAT150',
            'discount_type' => 'fixed',
            'discount_amount' => 150,
            'min_order_amount' => 600,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(7),
            'is_active' => true,
        ]);

        $check = $coupon->isValidForAmount(800, $user->id);
        $this->assertTrue($check['valid']);
        $this->assertEquals(150, $check['discount']);
    }
}
