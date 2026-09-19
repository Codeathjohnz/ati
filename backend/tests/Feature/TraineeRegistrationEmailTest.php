<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for a live bug: registering a trainee with a login
 * whose email already belonged to another account passed validation (only
 * username uniqueness was checked) and then failed at the database's unique
 * users.email index, surfacing as a bare "Server Error" (HTTP 500).
 */
class TraineeRegistrationEmailTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Dether John',
            'last_name' => 'Olaguir',
            'sex' => 'Male',
            'city_municipality' => 'Butuan City',
            'province' => 'Agusan del Norte',
            'create_login' => true,
            'username' => 'djolaguir',
            'password' => 'Trainee@12345',
        ], $overrides);
    }

    public function test_registering_with_a_login_email_already_in_use_is_a_validation_error_not_a_500(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($officer, 'sanctum')
            ->postJson('/api/v1/trainees', $this->payload(['email' => 'taken@example.com']));

        $response->assertStatus(422)->assertJsonValidationErrors('email');
        $this->assertDatabaseMissing('users', ['username' => 'djolaguir']);
        $this->assertDatabaseCount('trainee_profiles', 0);
    }

    public function test_registering_without_a_login_may_reuse_an_existing_accounts_email(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        User::factory()->create(['email' => 'shared@example.com']);

        $response = $this->actingAs($officer, 'sanctum')->postJson('/api/v1/trainees', $this->payload([
            'create_login' => false,
            'email' => 'shared@example.com',
            'username' => null,
            'password' => null,
        ]));

        $response->assertCreated();
    }

    public function test_registering_with_a_login_and_a_fresh_email_works(): void
    {
        $officer = User::factory()->projectOfficer()->create();

        $this->actingAs($officer, 'sanctum')
            ->postJson('/api/v1/trainees', $this->payload(['email' => 'fresh@example.com']))
            ->assertCreated();

        $this->assertDatabaseHas('users', ['username' => 'djolaguir', 'email' => 'fresh@example.com']);
    }

    public function test_setting_up_portal_access_with_an_email_in_use_is_a_422_not_a_500(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        User::factory()->create(['email' => 'taken@example.com']);
        $trainee = TraineeProfile::factory()->create(['user_id' => null]);

        $this->actingAs($officer, 'sanctum')
            ->postJson("/api/v1/trainees/{$trainee->id}/link-account", [
                'username' => 'newlogin',
                'email' => 'taken@example.com',
                'password' => 'Trainee@12345',
            ])
            ->assertStatus(422);

        $this->assertNull($trainee->fresh()->user_id);
    }
}
