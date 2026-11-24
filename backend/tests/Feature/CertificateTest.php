<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\RpEvaluation;
use App\Models\TraineeProfile;
use App\Models\TraineeTestAttempt;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramTest;
use App\Models\TrainingRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    private function register(TraineeProfile $trainee, TrainingProgram $program): void
    {
        TrainingRegistration::factory()->create(['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);
    }

    private function attend(TraineeProfile $trainee, TrainingProgram $program): TrainingHistory
    {
        return TrainingHistory::factory()->create([
            'trainee_id' => $trainee->id,
            'training_program_id' => $program->id,
            'chain_levels' => $program->chain_levels,
        ]);
    }

    private function publishTest(TrainingProgram $program, string $type): TrainingProgramTest
    {
        return TrainingProgramTest::create([
            'training_program_id' => $program->id,
            'type' => $type,
            'title' => ucfirst($type),
            'passing_score' => 70,
            'is_published' => true,
        ]);
    }

    private function passTest(TrainingHistory $history, TrainingProgramTest $test): void
    {
        TraineeTestAttempt::create([
            'training_program_test_id' => $test->id,
            'trainee_id' => $history->trainee_id,
            'training_history_id' => $history->id,
            'answers' => [],
            'raw_score' => 10,
            'max_score' => 10,
            'percentage' => 100,
            'passed' => true,
            'attempted_at' => now(),
        ]);
    }

    private function evaluate(TrainingHistory $history): void
    {
        RpEvaluation::factory()->create(['trainee_id' => $history->trainee_id, 'training_history_id' => $history->id]);
    }

    /** Fully completes every step (no pre/post test configured) and returns [trainee, program]. */
    private function completeTrainee(): array
    {
        $trainee = TraineeProfile::factory()->create();
        $program = TrainingProgram::factory()->create();
        $this->register($trainee, $program);
        $history = $this->attend($trainee, $program);
        $this->evaluate($history);

        return [$trainee, $program];
    }

    public function test_an_unregistered_trainee_is_not_issued_a_certificate(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create();
        $program = TrainingProgram::factory()->create();
        $history = $this->attend($trainee, $program);
        $this->evaluate($history);

        $this->actingAs($officer, 'sanctum')
            ->getJson("/api/v1/trainees/{$trainee->id}/training-programs/{$program->id}/certificate")
            ->assertStatus(404);

        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_a_registered_but_unattended_trainee_is_not_issued_a_certificate(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create();
        $program = TrainingProgram::factory()->create();
        $this->register($trainee, $program);

        $this->actingAs($officer, 'sanctum')
            ->getJson("/api/v1/trainees/{$trainee->id}/training-programs/{$program->id}/certificate")
            ->assertStatus(404);
    }

    public function test_attending_without_submitting_the_evaluation_is_not_enough(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create();
        $program = TrainingProgram::factory()->create();
        $this->register($trainee, $program);
        $this->attend($trainee, $program);

        $this->actingAs($officer, 'sanctum')
            ->getJson("/api/v1/trainees/{$trainee->id}/training-programs/{$program->id}/certificate")
            ->assertStatus(404);
    }

    public function test_a_published_test_not_yet_passed_blocks_the_certificate(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create();
        $program = TrainingProgram::factory()->create();
        $this->register($trainee, $program);
        $history = $this->attend($trainee, $program);
        $this->evaluate($history);
        $this->publishTest($program, TrainingProgramTest::TYPE_PRE);

        $this->actingAs($officer, 'sanctum')
            ->getJson("/api/v1/trainees/{$trainee->id}/training-programs/{$program->id}/certificate")
            ->assertStatus(404);
    }

    public function test_completing_registration_attendance_and_evaluation_issues_a_certificate_when_no_tests_are_configured(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        [$trainee, $program] = $this->completeTrainee();

        $response = $this->actingAs($officer, 'sanctum')->getJson("/api/v1/trainees/{$trainee->id}/training-programs/{$program->id}/certificate");

        $response->assertOk();
        $this->assertDatabaseHas('certificates', ['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);
        $this->assertStringStartsWith('ATI-CARAGA-', $response->json('certificate_number'));
    }

    public function test_completing_every_step_including_published_tests_issues_a_certificate(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create();
        $program = TrainingProgram::factory()->create();
        $this->register($trainee, $program);
        $history = $this->attend($trainee, $program);
        $this->evaluate($history);
        $pre = $this->publishTest($program, TrainingProgramTest::TYPE_PRE);
        $post = $this->publishTest($program, TrainingProgramTest::TYPE_POST);
        $this->passTest($history, $pre);
        $this->passTest($history, $post);

        $this->actingAs($officer, 'sanctum')
            ->getJson("/api/v1/trainees/{$trainee->id}/training-programs/{$program->id}/certificate")
            ->assertOk();

        $this->assertDatabaseHas('certificates', ['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);
    }

    public function test_a_trainee_is_never_issued_more_than_one_certificate_for_the_same_program(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        [$trainee, $program] = $this->completeTrainee();

        $first = $this->actingAs($officer, 'sanctum')
            ->getJson("/api/v1/trainees/{$trainee->id}/training-programs/{$program->id}/certificate")
            ->json('certificate_number');
        $second = $this->actingAs($officer, 'sanctum')
            ->getJson("/api/v1/trainees/{$trainee->id}/training-programs/{$program->id}/certificate")
            ->json('certificate_number');

        $this->assertSame($first, $second);
        $this->assertDatabaseCount('certificates', 1);
    }

    public function test_a_trainee_can_download_their_own_certificate_as_a_pdf(): void
    {
        $user = User::factory()->trainee()->create();
        [$trainee, $program] = $this->completeTrainee();
        $trainee->update(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->get("/api/v1/trainee/me/training-programs/{$program->id}/certificate/download");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_the_public_verify_endpoint_confirms_a_real_certificate_without_auth(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        [$trainee, $program] = $this->completeTrainee();
        $this->actingAs($officer, 'sanctum')->getJson("/api/v1/trainees/{$trainee->id}/training-programs/{$program->id}/certificate");

        $number = Certificate::first()->certificate_number;

        // No actingAs() at all — this must work for a fully anonymous request.
        $response = $this->getJson("/api/v1/verify/{$number}");

        $response->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('trainee_name', $trainee->full_name)
            ->assertJsonPath('training_program_name', $program->name);
    }

    public function test_the_public_verify_endpoint_rejects_an_unknown_certificate_number(): void
    {
        $this->getJson('/api/v1/verify/ATI-CARAGA-2026-999999')
            ->assertStatus(404)
            ->assertJsonPath('valid', false);
    }
}
