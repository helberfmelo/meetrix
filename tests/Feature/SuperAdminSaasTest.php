<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuperAdminSaasTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_list_customers_and_apply_actions(): void
    {
        $admin = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'is_super_admin' => false,
            'is_active' => true,
        ]);

        Sanctum::actingAs($admin);

        $customersResponse = $this->getJson('/api/super-admin/customers');
        $customersResponse->assertStatus(200)
            ->assertJsonFragment(['email' => $customer->email]);

        $actionResponse = $this->postJson("/api/super-admin/customers/{$customer->id}/actions", [
            'action' => 'deactivate',
            'reason' => 'teste',
        ]);

        $actionResponse->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_user_id' => $admin->id,
            'target_user_id' => $customer->id,
            'action' => 'deactivate',
        ]);
    }

    public function test_non_super_admin_cannot_access_super_admin_routes(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/super-admin/overview');

        $response->assertStatus(403);
    }
}
