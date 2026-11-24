<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\TrainingProgram;
use App\Models\TrainingRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function asTrainee(): array
    {
        $user = User::factory()->create(['role' => User::ROLE_TRAINEE]);
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);

        return [$user, $trainee];
    }

    public function test_a_trainee_sees_only_posted_trainings_flagged_with_their_own_registration_status(): void
    {
        [$user, $trainee] = $this->asTrainee();
        $posted = TrainingProgram::factory()->create(['is_posted' => true]);
        TrainingProgram::factory()->create(['is_posted' => false]);
        TrainingRegistration::factory()->create(['trainee_id' => $trainee->id, 'training_program_id' => $posted->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/trainee/me/training-programs-posted');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertTrue($response->json('data.0.is_registered'));
    }

    public function test_a_trainee_can_register_for_a_posted_training(): void
    {
        [$user, $trainee] = $this->asTrainee();
        $program = TrainingProgram::factory()->create(['is_posted' => true]);

        $response = $this->actingAs($user)->postJson("/api/v1/trainee/me/training-programs/{$program->id}/register");

        $response->assertCreated();
        $this->assertDatabaseHas('training_registrations', [
            'trainee_id' => $trainee->id,
            'training_program_id' => $program->id,
        ]);
    }

    public function test_registering_twice_does_not_create_a_duplicate_row(): void
    {
        [$user, $trainee] = $this->asTrainee();
        $program = TrainingProgram::factory()->create(['is_posted' => true]);

        $this->actingAs($user)->postJson("/api/v1/trainee/me/training-programs/{$program->id}/register");
        $this->actingAs($user)->postJson("/api/v1/trainee/me/training-programs/{$program->id}/register");

        $this->assertSame(1, TrainingRegistration::where('trainee_id', $trainee->id)->count());
    }

    public function test_a_trainee_cannot_register_for_a_training_that_is_not_posted(): void
    {
        [$user] = $this->asTrainee();
        $program = TrainingProgram::factory()->create(['is_posted' => false]);

        $this->actingAs($user)
            ->postJson("/api/v1/trainee/me/training-programs/{$program->id}/register")
            ->assertStatus(422);
    }

    public function test_a_trainee_can_withdraw_a_registration(): void
    {
        [$user, $trainee] = $this->asTrainee();
        $program = TrainingProgram::factory()->create(['is_posted' => true]);
        TrainingRegistration::factory()->create(['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);

        $this->actingAs($user)->deleteJson("/api/v1/trainee/me/training-programs/{$program->id}/register")->assertOk();

        $this->assertDatabaseMissing('training_registrations', [
            'trainee_id' => $trainee->id,
            'training_program_id' => $program->id,
        ]);
    }

    public function test_an_officer_can_list_registrants_for_a_training_program(): void
    {
        $officer = User::factory()->create(['role' => User::ROLE_PROJECT_OFFICER]);
        [, $trainee] = $this->asTrainee();
        $program = TrainingProgram::factory()->create(['is_posted' => true]);
        TrainingRegistration::factory()->create(['trainee_id' => $trainee->id, 'training_program_id' => $program->id]);

        $response = $this->actingAs($officer)->getJson("/api/v1/training-programs/{$program->id}/registrations");

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.trainee_id', $trainee->id);
    }
}
