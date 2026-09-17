<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_log_in_with_valid_credentials_and_receive_a_token(): void
    {
        User::factory()->admin()->create(['username' => 'admin1', 'password' => Hash::make('secret123')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin1',
            'password' => 'secret123',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'role']]);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->admin()->create(['username' => 'admin2', 'password' => Hash::make('secret123')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin2',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_a_deactivated_account_cannot_log_in(): void
    {
        User::factory()->admin()->create([
            'username' => 'admin3',
            'password' => Hash::make('secret123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin3',
            'password' => 'secret123',
        ]);

        $response->assertStatus(403);
    }

    public function test_a_project_officer_cannot_access_admin_only_routes(): void
    {
        $officer = User::factory()->projectOfficer()->create();

        // /dashboard is intentionally shared with project officers; /admin/users is not.
        $response = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    public function test_a_project_officer_can_access_the_shared_dashboard(): void
    {
        $officer = User::factory()->projectOfficer()->create();

        $response = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/dashboard');

        $response->assertOk();
    }

    public function test_a_trainee_can_only_see_their_own_profile_via_the_self_service_endpoint(): void
    {
        $user = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/trainee/me');

        $response->assertOk()->assertJsonPath('data.id', $trainee->id);
    }

    public function test_a_trainee_cannot_access_the_project_officer_trainee_listing(): void
    {
        $user = User::factory()->trainee()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/trainees');

        $response->assertStatus(403);
    }
}
