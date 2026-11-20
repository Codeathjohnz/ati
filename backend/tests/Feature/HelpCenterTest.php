<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_trainee_gets_the_trainee_manual(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TRAINEE]);
        TraineeProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/help/manual');

        $response->assertOk()->assertJsonPath('title', 'Trainee User Manual');
        $this->assertStringContainsString('Downloading Your Certificate', $response->json('html'));
    }

    public function test_an_officer_gets_the_officer_manual(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROJECT_OFFICER]);

        $response = $this->actingAs($user)->getJson('/api/v1/help/manual');

        $response->assertOk()->assertJsonPath('title', 'Project Officer User Manual');
        $this->assertStringContainsString('Recording Training Attendance', $response->json('html'));
    }

    public function test_an_admin_gets_the_admin_manual(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)->getJson('/api/v1/help/manual');

        $response->assertOk()->assertJsonPath('title', 'Administrator User Manual');
        $this->assertStringContainsString('Managing User Accounts', $response->json('html'));
    }

    public function test_the_manual_pdf_renders_inline_for_an_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TRAINEE]);
        TraineeProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/api/v1/help/manual/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_an_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/v1/help/manual')->assertStatus(401);
        $this->getJson('/api/v1/help/manual/pdf')->assertStatus(401);
    }
}
