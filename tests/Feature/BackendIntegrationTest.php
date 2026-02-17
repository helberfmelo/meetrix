<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_scheduling_page()
    {
        $user = User::factory()->create();
        
        // Manual token creation to bypass helper issues
        $token = $user->createToken('test-token')->plainTextToken;

        $tenant = Tenant::create([
            'user_id' => $user->id,
            'name' => 'Test Team',
            'slug' => 'test-team-'.rand(1000,9999)
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/pages', [
            'title' => 'Consultation Call',
            'slug' => 'consultation-call-'.rand(1000,9999), 
            'description' => '30 min meeting',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Consultation Call']);

        $this->assertDatabaseHas('pages', ['title' => 'Consultation Call']);
    }

    public function test_public_can_view_page()
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Test', 'slug' => 'test']);
        $page = Page::create([
            'tenant_id' => $tenant->id,
            'title' => 'Public Page',
            'slug' => 'public-page',
            'is_active' => true,
            'config' => ['duration' => 30]
        ]);

        $response = $this->getJson('/api/p/public-page');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Public Page']);
    }

    public function test_public_can_book_appointment()
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Test', 'slug' => 'test']);
        $page = Page::create([
            'tenant_id' => $tenant->id,
            'title' => 'Booking Page',
            'slug' => 'booking-page',
            'is_active' => true,
            'config' => ['duration' => 30]
        ]);

        $startTime = now()->addDay()->setHour(10)->setMinute(0);

        $response = $this->postJson('/api/bookings', [
            'page_slug' => 'booking-page',
            'start_time' => $startTime->toIso8601String(),
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookings', [
            'customer_email' => 'john@example.com',
            'page_id' => $page->id
        ]);
    }
}
