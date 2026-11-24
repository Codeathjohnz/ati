<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class AttendanceQrTest extends TestCase
{
    use RefreshDatabase;

    private function asTrainee(): array
    {
        $user = User::factory()->create(['role' => User::ROLE_TRAINEE]);
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);

        return [$user, $trainee];
    }

    private function tokenFor(TrainingProgram $program, int $expiresInSeconds = 3600): string
    {
        return Crypt::encryptString(json_encode([
            'training_program_id' => $program->id,
            'expires_at' => now()->addSeconds($expiresInSeconds)->timestamp,
        ]));
    }

    public function test_an_officer_can_generate_an_attendance_qr_code(): void
    {
        $officer = User::factory()->create(['role' => User::ROLE_PROJECT_OFFICER]);
        $program = TrainingProgram::factory()->create();

        $response = $this->actingAs($officer)->getJson("/api/v1/training-programs/{$program->id}/attendance-qr");

        $response->assertOk()->assertJsonStructure(['qr_data_uri', 'attend_url', 'expires_at']);
        $this->assertStringStartsWith('data:image/png;base64,', $response->json('qr_data_uri'));
    }

    public function test_a_trainee_scanning_a_valid_token_gets_attendance_recorded(): void
    {
        [$user, $trainee] = $this->asTrainee();
        $program = TrainingProgram::factory()->create(['chain_levels' => ['Production']]);

        $response = $this->actingAs($user)->postJson('/api/v1/trainee/me/attend', ['token' => $this->tokenFor($program)]);

        $response->assertCreated();
        $this->assertDatabaseHas('training_history', [
            'trainee_id' => $trainee->id,
            'training_program_id' => $program->id,
        ]);
    }

    public function test_scanning_the_same_token_twice_does_not_duplicate_attendance(): void
    {
        [$user, $trainee] = $this->asTrainee();
        $program = TrainingProgram::factory()->create();
        $token = $this->tokenFor($program);

        $this->actingAs($user)->postJson('/api/v1/trainee/me/attend', ['token' => $token]);
        $this->actingAs($user)->postJson('/api/v1/trainee/me/attend', ['token' => $token])->assertOk();

        $this->assertSame(
            1,
            TrainingHistory::where('trainee_id', $trainee->id)->where('training_program_id', $program->id)->count()
        );
    }

    public function test_an_expired_token_is_rejected(): void
    {
        [$user] = $this->asTrainee();
        $program = TrainingProgram::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/v1/trainee/me/attend', ['token' => $this->tokenFor($program, -60)])
            ->assertStatus(422);
    }

    public function test_a_garbage_token_is_rejected(): void
    {
        [$user] = $this->asTrainee();

        $this->actingAs($user)
            ->postJson('/api/v1/trainee/me/attend', ['token' => 'not-a-real-token'])
            ->assertStatus(422);
    }
}
