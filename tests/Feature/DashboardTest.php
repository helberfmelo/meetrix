<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SchedulingPage;
use App\Models\Booking;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_aggregation()
    {
        $user = User::factory()->create();
        
        // Ensure tenant exists (as AuthController creates it)
        $user->tenant()->create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'region' => 'BR',
            'currency' => 'BRL',
        ]);

        $page = SchedulingPage::create([
            'user_id' => $user->id,
            'title' => 'Dashboard Test Page',
            'slug' => 'dash-test',
            'views' => 10,
            'slot_clicks' => 5,
        ]);

        Booking::create([
            'scheduling_page_id' => $page->id,
            'customer_name' => 'Customer 1',
            'customer_email' => 'c1@example.com',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'status' => 'confirmed',
            'timezone' => 'UTC',
        ]);

        $response = $this->actingAs($user)->getJson('/api/dashboard/stats');

        $response->assertStatus(200);
        $response->assertJson([
            'funnel' => [
                'views' => 10,
                'clicks' => 5,
                'bookings' => 1,
            ],
            'stats' => [
                'total_bookings' => 1,
                'upcoming' => 1,
            ]
        ]);
    }
}
