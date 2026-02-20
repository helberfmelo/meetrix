<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use App\Models\SchedulingPage;
use App\Models\AppointmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Coupon CRUD via API.
     */
    public function test_coupon_crud_api()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/coupons', [
            'code' => 'TEST100',
            'discount_type' => 'percent',
            'discount_value' => 100,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('coupons', ['code' => 'TEST100']);
    }

    /**
     * Test 100% discount bypasses Stripe for subscriptions.
     */
    public function test_subscription_100_percent_discount_bypasses_stripe()
    {
        $user = User::factory()->create();
        
        $coupon = Coupon::create([
            'code' => 'FREEPRO',
            'discount_type' => 'percent',
            'discount_value' => 100,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/api/subscription/checkout', [
            'plan' => 'pro',
            'interval' => 'monthly',
            'coupon_code' => 'FREEPRO',
        ]);

        // Should be confirmed immediately, return success and redirect
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'redirect_url' => url('/dashboard?subscription=free_success')
        ]);
        
        $this->assertEquals('pro', $user->fresh()->subscription_tier);
    }
}
