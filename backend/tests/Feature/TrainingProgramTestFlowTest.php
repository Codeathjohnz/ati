<?php

namespace Tests\Feature;

use App\Models\DocumentSubmission;
use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingProgramTestFlowTest extends TestCase
{
    use RefreshDatabase;

    private function questionPayload(): array
    {
        return [
            'title' => 'Production Basics Pre-Test',
            'instructions' => 'Answer all items.',
            'passing_score' => 70,
            'questions' => [
                ['type' => 'multiple_choice', 'question_text' => 'Best soil pH for rice?', 'options' => ['5.5-6.5', '9-10'], 'correct_answer' => '5.5-6.5', 'points' => 1],
                ['type' => 'fill_blank', 'question_text' => 'Fill in: ___ increases yield.', 'correct_answer' => 'Fertilizer', 'points' => 1],
                ['type' => 'identification', 'question_text' => 'Name the first chain level.', 'correct_answer' => 'Production', 'points' => 1],
            ],
        ];
    }

    public function test_an_officer_can_build_a_pre_test_with_an_answer_key(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();

        $response = $this->actingAs($officer, 'sanctum')
            ->putJson("/api/v1/training-programs/{$program->id}/tests/pre_test", $this->questionPayload());

        $response->assertOk();
        $this->assertDatabaseHas('training_program_tests', ['training_program_id' => $program->id, 'type' => 'pre_test', 'is_published' => false]);
        $this->assertDatabaseCount('test_questions', 3);
    }

    public function test_a_saved_but_unpublished_test_is_hidden_from_trainees(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();
        $this->actingAs($officer, 'sanctum')->putJson("/api/v1/training-programs/{$program->id}/tests/pre_test", $this->questionPayload());

        $traineeUser = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $traineeUser->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);

        $response = $this->actingAs($traineeUser, 'sanctum')->getJson("/api/v1/trainee/me/training-history/{$history->id}/tests/pre_test");

        $response->assertOk();
        $body = $response->json();
        $this->assertTrue($body['exists']);
        $this->assertFalse($body['published']);
        $this->assertArrayNotHasKey('test', $body);
    }

    public function test_a_trainee_cannot_attempt_an_unpublished_test(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();
        $this->actingAs($officer, 'sanctum')->putJson("/api/v1/training-programs/{$program->id}/tests/pre_test", $this->questionPayload());

        $traineeUser = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $traineeUser->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);

        $this->actingAs($traineeUser, 'sanctum')
            ->postJson("/api/v1/trainee/me/training-history/{$history->id}/tests/pre_test/attempt", ['answers' => []])
            ->assertStatus(422);
    }

    public function test_publishing_makes_a_draft_visible_and_takeable_and_unpublishing_hides_it_again(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();
        $this->actingAs($officer, 'sanctum')->putJson("/api/v1/training-programs/{$program->id}/tests/pre_test", $this->questionPayload());

        $this->actingAs($officer, 'sanctum')
            ->postJson("/api/v1/training-programs/{$program->id}/tests/pre_test/publish")
            ->assertOk()
            ->assertJsonPath('is_published', true);

        $traineeUser = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $traineeUser->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);

        $visible = $this->actingAs($traineeUser, 'sanctum')->getJson("/api/v1/trainee/me/training-history/{$history->id}/tests/pre_test");
        $visible->assertOk()->assertJsonPath('published', true);
        $this->assertCount(3, $visible->json('test.questions'));

        $this->actingAs($officer, 'sanctum')
            ->postJson("/api/v1/training-programs/{$program->id}/tests/pre_test/unpublish")
            ->assertOk()
            ->assertJsonPath('is_published', false);

        $hiddenAgain = $this->actingAs($traineeUser, 'sanctum')->getJson("/api/v1/trainee/me/training-history/{$history->id}/tests/pre_test");
        $hiddenAgain->assertOk()->assertJsonPath('published', false);
    }

    public function test_publishing_a_test_with_no_questions_is_refused(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();
        TrainingProgramTest::create([
            'training_program_id' => $program->id,
            'type' => 'pre_test',
            'title' => 'Empty Test',
            'passing_score' => 70,
        ]);

        $this->actingAs($officer, 'sanctum')
            ->postJson("/api/v1/training-programs/{$program->id}/tests/pre_test/publish")
            ->assertStatus(422);
    }

    public function test_a_trainee_never_receives_the_answer_key(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();
        $this->actingAs($officer, 'sanctum')->putJson("/api/v1/training-programs/{$program->id}/tests/pre_test", $this->questionPayload());
        $this->actingAs($officer, 'sanctum')->postJson("/api/v1/training-programs/{$program->id}/tests/pre_test/publish");

        $traineeUser = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $traineeUser->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);

        $response = $this->actingAs($traineeUser, 'sanctum')->getJson("/api/v1/trainee/me/training-history/{$history->id}/tests/pre_test");

        $response->assertOk();
        $body = $response->json();
        $this->assertTrue($body['exists']);
        foreach ($body['test']['questions'] as $question) {
            $this->assertArrayNotHasKey('correct_answer', $question);
        }
    }

    public function test_passing_a_test_approves_the_matching_document_and_failing_flags_it_for_revision(): void
    {
        RequiredDocument::create(['code' => 'pre_test', 'name' => 'Pre-Test', 'is_active' => true]);
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();
        $this->actingAs($officer, 'sanctum')->putJson("/api/v1/training-programs/{$program->id}/tests/pre_test", $this->questionPayload());
        $this->actingAs($officer, 'sanctum')->postJson("/api/v1/training-programs/{$program->id}/tests/pre_test/publish");
        $test = $program->tests()->where('type', 'pre_test')->with('questions')->first();
        $questions = $test->questions->keyBy('question_text');

        $traineeUser = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $traineeUser->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);

        // Fail first: all three answers wrong.
        $this->actingAs($traineeUser, 'sanctum')->postJson(
            "/api/v1/trainee/me/training-history/{$history->id}/tests/pre_test/attempt",
            ['answers' => [
                $questions['Best soil pH for rice?']->id => '9-10',
                $questions['Fill in: ___ increases yield.']->id => 'wrong',
                $questions['Name the first chain level.']->id => 'wrong',
            ]]
        )->assertOk();

        $submission = DocumentSubmission::where('trainee_id', $trainee->id)->first();
        $this->assertSame('needs_revision', $submission->review_status);

        // Retake and pass: all three answers correct — latest attempt wins.
        $this->actingAs($traineeUser, 'sanctum')->postJson(
            "/api/v1/trainee/me/training-history/{$history->id}/tests/pre_test/attempt",
            ['answers' => [
                $questions['Best soil pH for rice?']->id => '5.5-6.5',
                $questions['Fill in: ___ increases yield.']->id => 'Fertilizer',
                $questions['Name the first chain level.']->id => 'production',
            ]]
        )->assertOk();

        $this->assertSame('approved', $submission->fresh()->review_status);
        $this->assertDatabaseCount('trainee_test_attempts', 2);
    }

    public function test_a_trainee_cannot_take_a_test_for_someone_elses_training_history(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();
        $this->actingAs($officer, 'sanctum')->putJson("/api/v1/training-programs/{$program->id}/tests/pre_test", $this->questionPayload());
        $this->actingAs($officer, 'sanctum')->postJson("/api/v1/training-programs/{$program->id}/tests/pre_test/publish");

        $ownerUser = User::factory()->trainee()->create();
        $owner = TraineeProfile::factory()->create(['user_id' => $ownerUser->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $owner->id, 'training_program_id' => $program->id]);

        $intruderUser = User::factory()->trainee()->create();
        TraineeProfile::factory()->create(['user_id' => $intruderUser->id]);

        $this->actingAs($intruderUser, 'sanctum')
            ->postJson("/api/v1/trainee/me/training-history/{$history->id}/tests/pre_test/attempt", ['answers' => []])
            ->assertStatus(403);
    }
}
