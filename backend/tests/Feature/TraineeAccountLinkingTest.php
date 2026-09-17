<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraineeAccountLinkingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_project_officer_can_link_a_login_account_to_an_existing_trainee_profile(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => null]);

        $response = $this->actingAs($officer, 'sanctum')->postJson("/api/v1/trainees/{$trainee->id}/link-account", [
            'username' => 'newlogin',
            'password' => 'Secret@123',
        ]);

        $response->assertOk()->assertJsonPath('data.has_login', true);

        $trainee->refresh();
        $this->assertNotNull($trainee->user_id);

        $linkedUser = User::find($trainee->user_id);
        $this->assertSame('newlogin', $linkedUser->username);
        $this->assertSame(User::ROLE_TRAINEE, $linkedUser->role);

        // The linked account can now log in and see this exact profile.
        $loginResponse = $this->postJson('/api/v1/auth/login', ['username' => 'newlogin', 'password' => 'Secret@123']);
        $loginResponse->assertOk();

        $meResponse = $this->actingAs($linkedUser, 'sanctum')->getJson('/api/v1/trainee/me');
        $meResponse->assertOk()->assertJsonPath('data.id', $trainee->id);
    }

    public function test_linking_an_account_twice_is_rejected(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $existingUser = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $existingUser->id]);

        $response = $this->actingAs($officer, 'sanctum')->postJson("/api/v1/trainees/{$trainee->id}/link-account", [
            'username' => 'anotherlogin',
            'password' => 'Secret@123',
        ]);

        $response->assertStatus(422);
    }
}
