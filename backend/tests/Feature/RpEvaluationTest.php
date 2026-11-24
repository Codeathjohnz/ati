<?php

namespace Tests\Feature;

use App\Models\RpEvaluation;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RpEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private function likertPayload(array $overrides = []): array
    {
        return array_merge([
            'clarity_objectives' => 5,
            'topic_organization' => 5,
            'clarity_presentation' => 5,
            'instructional_aids_quality' => 5,
            'teaching_ability' => 5,
            'question_answering_ability' => 5,
            'participant_interest' => 5,
            'time_management' => 5,
            'topic_ending' => 5,
            'overall_satisfaction' => 5,
            'liked_about_rp' => 'Very clear explanations.',
            'disliked_about_rp' => 'N/A',
            'other_remarks' => 'N/A',
        ], $overrides);
    }

    public function test_a_trainee_can_submit_an_evaluation_for_their_own_attendance(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TRAINEE]);
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id]);

        $payload = $this->likertPayload([
            'topic_ratings' => [[
                'topic' => 'Fertility Management',
                'relevance' => 'Very Relevant',
                'time_allocation' => 'Just right',
                'suggested_time' => null,
                'methodology' => 'Effective',
                'suggested_methodology' => null,
            ]],
        ]);

        $response = $this->actingAs($user)->postJson("/api/v1/trainee/me/training-history/{$history->id}/evaluation", $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('rp_evaluations', [
            'trainee_id' => $trainee->id,
            'training_history_id' => $history->id,
            'overall_satisfaction' => 5,
        ]);
    }

    public function test_a_trainee_cannot_submit_an_evaluation_twice_for_the_same_attendance(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TRAINEE]);
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id]);
        RpEvaluation::factory()->create(['trainee_id' => $trainee->id, 'training_history_id' => $history->id]);

        $this->actingAs($user)
            ->postJson("/api/v1/trainee/me/training-history/{$history->id}/evaluation", $this->likertPayload())
            ->assertStatus(422);
    }

    public function test_a_trainee_cannot_submit_an_evaluation_for_someone_elses_attendance(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TRAINEE]);
        TraineeProfile::factory()->create(['user_id' => $user->id]);
        $otherHistory = TrainingHistory::factory()->create();

        $this->actingAs($user)
            ->postJson("/api/v1/trainee/me/training-history/{$otherHistory->id}/evaluation", $this->likertPayload())
            ->assertStatus(403);
    }

    public function test_a_likert_rating_outside_one_to_five_is_rejected(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TRAINEE]);
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id]);

        $this->actingAs($user)
            ->postJson(
                "/api/v1/trainee/me/training-history/{$history->id}/evaluation",
                $this->likertPayload(['overall_satisfaction' => 9])
            )
            ->assertStatus(422);
    }

    public function test_a_trainee_can_view_their_own_submitted_evaluation(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_TRAINEE]);
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id]);
        RpEvaluation::factory()->create(['trainee_id' => $trainee->id, 'training_history_id' => $history->id, 'overall_satisfaction' => 4]);

        $response = $this->actingAs($user)->getJson("/api/v1/trainee/me/training-history/{$history->id}/evaluation");

        $response->assertOk()->assertJsonPath('data.overall_satisfaction', 4);
    }
}
