<?php

namespace Tests\Feature;

use App\Models\AppointmentType;
use App\Models\SchedulingPage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BackendIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_scheduling_page(): void
    {
        $user = User::factory()->create();

        Tenant::create([
            'user_id' => $user->id,
            'name' => 'Test Tenant',
            'slug' => 'test-tenant-' . rand(1000, 9999),
            'region' => 'BR',
            'currency' => 'BRL',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/pages', [
            'title' => 'Consultation Call',
            'slug' => 'consultation-call-' . rand(1000, 9999),
            'intro_text' => '30 min meeting',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Consultation Call']);

        $this->assertDatabaseHas('scheduling_pages', ['title' => 'Consultation Call']);
    }

    public function test_public_can_view_page(): void
    {
        $user = User::factory()->create();

        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'title' => 'Public Page',
            'slug' => 'public-page',
            'is_active' => true,
            'config' => ['duration' => 30],
        ]);

        AppointmentType::create([
            'scheduling_page_id' => $page->id,
            'name' => 'Quick Chat',
            'duration_minutes' => 30,
            'price' => 0,
            'currency' => 'BRL',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/p/public-page');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Public Page']);
    }

    public function test_public_can_book_free_appointment(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'title' => 'Booking Page',
            'slug' => 'booking-page',
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
            'name' => 'Intro Call',
            'duration_minutes' => 30,
            'price' => 0,
            'currency' => 'BRL',
            'is_active' => true,
        ]);

        $startTime = now()->addDay()->setHour(10)->setMinute(0)->setSecond(0);

        $response = $this->postJson('/api/bookings', [
            'scheduling_page_id' => $page->id,
            'appointment_type_id' => $type->id,
            'start_at' => $startTime->toIso8601String(),
            'timezone' => 'UTC',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('booking.customer_email', 'john@example.com');

        $this->assertDatabaseHas('bookings', [
            'customer_email' => 'john@example.com',
            'scheduling_page_id' => $page->id,
            'appointment_type_id' => $type->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('billing_transactions', [
            'user_id' => $user->id,
            'source' => 'booking',
            'status' => 'paid',
        ]);
    }
}
