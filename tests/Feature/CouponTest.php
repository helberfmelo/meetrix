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
     * Test 100% discount bypasses Stripe.
     */
    public function test_100_percent_discount_bypasses_stripe()
    {
        $user = User::factory()->create();
        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'title' => 'Test Page',
            'slug' => 'test-page',
        ]);
        
        $type = AppointmentType::create([
            'scheduling_page_id' => $page->id,
            'name' => 'Paid Service',
            'price' => 50,
            'duration_minutes' => 30,
        ]);

        $coupon = Coupon::create([
            'code' => 'FREEPASS',
            'discount_type' => 'percent',
            'discount_value' => 100,
        ]);

        $response = $this->postJson('/api/bookings', [
            'scheduling_page_id' => $page->id,
            'appointment_type_id' => $type->id,
            'customer_name' => 'Tester',
            'customer_email' => 'test@example.com',
            'start_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'coupon_code' => 'FREEPASS',
            'timezone' => 'UTC',
        ]);

        // Should be confirmed immediately, not require payment
        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'customer_name' => 'Tester',
            'status' => 'confirmed'
        ]);
        $this->assertArrayNotHasKey('checkout_url', $response->json());
    }
}
