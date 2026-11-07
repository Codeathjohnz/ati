<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    private function completeTrainee(): TraineeProfile
    {
        $trainee = TraineeProfile::factory()->create();
        foreach (TrainingProgram::CHAIN_LEVELS as $level) {
            TrainingHistory::factory()->create(['trainee_id' => $trainee->id, 'chain_level' => $level]);
        }

        return $trainee;
    }

    public function test_a_trainee_with_remaining_gaps_is_not_issued_a_certificate(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create();
        TrainingHistory::factory()->create(['trainee_id' => $trainee->id, 'chain_level' => 'Production']);

        $this->actingAs($officer, 'sanctum')
            ->getJson("/api/v1/trainees/{$trainee->id}/certificate")
            ->assertStatus(404);

        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_completing_all_five_chain_levels_issues_a_certificate_automatically(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = $this->completeTrainee();

        $response = $this->actingAs($officer, 'sanctum')->getJson("/api/v1/trainees/{$trainee->id}/certificate");

        $response->assertOk();
        $this->assertDatabaseHas('certificates', ['trainee_id' => $trainee->id]);
        $this->assertStringStartsWith('ATI-CARAGA-', $response->json('certificate_number'));
        $this->assertCount(5, $response->json('chain_levels'));
    }

    public function test_a_trainee_is_never_issued_more_than_one_certificate(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = $this->completeTrainee();

        $first = $this->actingAs($officer, 'sanctum')->getJson("/api/v1/trainees/{$trainee->id}/certificate")->json('certificate_number');
        $second = $this->actingAs($officer, 'sanctum')->getJson("/api/v1/trainees/{$trainee->id}/certificate")->json('certificate_number');

        $this->assertSame($first, $second);
        $this->assertDatabaseCount('certificates', 1);
    }

    public function test_a_trainee_can_download_their_own_certificate_as_a_pdf(): void
    {
        $user = User::factory()->trainee()->create();
        $trainee = $this->completeTrainee();
        $trainee->update(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->get('/api/v1/trainee/me/certificate/download');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_the_public_verify_endpoint_confirms_a_real_certificate_without_auth(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = $this->completeTrainee();
        $this->actingAs($officer, 'sanctum')->getJson("/api/v1/trainees/{$trainee->id}/certificate");

        $number = Certificate::first()->certificate_number;

        // No actingAs() at all — this must work for a fully anonymous request.
        $response = $this->getJson("/api/v1/verify/{$number}");

        $response->assertOk()->assertJsonPath('valid', true)->assertJsonPath('trainee_name', $trainee->full_name);
    }

    public function test_the_public_verify_endpoint_rejects_an_unknown_certificate_number(): void
    {
        $this->getJson('/api/v1/verify/ATI-CARAGA-2026-999999')
            ->assertStatus(404)
            ->assertJsonPath('valid', false);
    }
}
