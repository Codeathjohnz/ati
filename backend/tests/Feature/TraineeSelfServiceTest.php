<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\TrainingConfirmation;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for a real bug found during manual verification: the
 * confirmations list/respond endpoints were switched to a JsonResource
 * collection, which Laravel wraps in {"data": [...]} when returned directly
 * from a controller — but the front end expected a bare array. These tests
 * pin down the actual wire shape so the two can never drift apart again.
 */
class TraineeSelfServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_confirmations_returns_a_data_wrapped_array_with_clean_dates(): void
    {
        $user = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);
        TrainingProgram::factory()->create([
            'status' => 'upcoming',
            'start_date' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/trainee/me/confirmations');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'status', 'training_program' => ['id', 'name', 'start_date']]]]);

        // The date must be a plain YYYY-MM-DD string, not a full ISO datetime.
        $startDate = $response->json('data.0.training_program.start_date');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $startDate);
        $this->assertSame($trainee->id, $response->json('data.0.trainee_id'));
    }

    public function test_responding_to_a_confirmation_returns_a_data_wrapped_resource(): void
    {
        $user = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);
        $program = TrainingProgram::factory()->create();
        $confirmation = TrainingConfirmation::create([
            'trainee_id' => $trainee->id,
            'training_program_id' => $program->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/trainee/me/confirmations/{$confirmation->id}/respond", ['status' => 'confirmed']);

        $response->assertOk()->assertJsonPath('data.status', 'confirmed');
    }

    public function test_a_trainee_cannot_respond_to_another_trainees_confirmation(): void
    {
        $owner = User::factory()->trainee()->create();
        $ownerTrainee = TraineeProfile::factory()->create(['user_id' => $owner->id]);
        $program = TrainingProgram::factory()->create();
        $confirmation = TrainingConfirmation::create([
            'trainee_id' => $ownerTrainee->id,
            'training_program_id' => $program->id,
            'status' => 'pending',
        ]);

        $intruder = User::factory()->trainee()->create();
        TraineeProfile::factory()->create(['user_id' => $intruder->id]);

        $response = $this->actingAs($intruder, 'sanctum')
            ->postJson("/api/v1/trainee/me/confirmations/{$confirmation->id}/respond", ['status' => 'confirmed']);

        $response->assertStatus(403);
    }
}
