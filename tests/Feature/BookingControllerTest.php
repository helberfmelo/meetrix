<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use App\Models\Coupon;
use App\Models\SchedulingPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_with_100_percent_coupon_confirms_without_stripe(): void
    {
        Mail::fake();

        $owner = User::factory()->create();

        $page = SchedulingPage::create([
            'user_id' => $owner->id,
            'slug' => 'coupon-test',
            'title' => 'Coupon Test',
            'is_active' => true,
            'config' => [
                'form_fields' => [
                    ['name' => 'customer_name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['name' => 'customer_email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ],
            ],
        ]);

        $type = AppointmentType::create([
            'scheduling_page_id' => $page->id,
            'name' => 'Premium Session',
            'duration_minutes' => 45,
            'price' => 100,
            'currency' => 'BRL',
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'FREE100',
            'discount_type' => 'percent',
            'discount_value' => 100,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/bookings', [
            'scheduling_page_id' => $page->id,
            'appointment_type_id' => $type->id,
            'start_at' => now()->addDay()->setHour(14)->setMinute(0)->setSecond(0)->toIso8601String(),
            'timezone' => 'UTC',
            'customer_name' => 'Coupon User',
            'customer_email' => 'coupon@example.com',
            'coupon_code' => 'FREE100',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('booking.status', 'confirmed');

        $this->assertDatabaseHas('billing_transactions', [
            'user_id' => $owner->id,
            'source' => 'booking',
            'status' => 'paid',
            'coupon_code' => 'FREE100',
        ]);

        $this->assertDatabaseHas('coupons', [
            'code' => 'FREE100',
            'times_used' => 1,
        ]);
    }

    public function test_booking_rejects_appointment_type_from_another_page(): void
    {
        $owner = User::factory()->create();

        $pageA = SchedulingPage::create([
            'user_id' => $owner->id,
            'slug' => 'page-a',
            'title' => 'Page A',
            'is_active' => true,
        ]);

        $pageB = SchedulingPage::create([
            'user_id' => $owner->id,
            'slug' => 'page-b',
            'title' => 'Page B',
            'is_active' => true,
        ]);

        $typeFromPageB = AppointmentType::create([
            'scheduling_page_id' => $pageB->id,
            'name' => 'Invalid Type',
            'duration_minutes' => 30,
            'price' => 0,
            'currency' => 'BRL',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/bookings', [
            'scheduling_page_id' => $pageA->id,
            'appointment_type_id' => $typeFromPageB->id,
            'start_at' => now()->addDay()->setHour(9)->setMinute(0)->setSecond(0)->toIso8601String(),
            'timezone' => 'UTC',
            'customer_name' => 'Invalid User',
            'customer_email' => 'invalid@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Selected service is not available for this page.');
    }
}
