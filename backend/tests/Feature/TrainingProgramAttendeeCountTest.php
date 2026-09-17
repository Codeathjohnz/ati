<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for a real bug: TrainingProgramResource and
 * TraineeProfileResource gated their counts on relationLoaded('trainingHistory'),
 * but the list/show controller actions only ever set a `*_count` attribute via
 * withCount()/loadCount() — the relation itself is never loaded there. The
 * counts were silently dropped from every JSON response and always rendered
 * as 0 in the UI, no matter how much real attendance data existed.
 */
class TrainingProgramAttendeeCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_training_program_list_reports_real_attendee_counts(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();
        TrainingHistory::factory()->count(3)->create(['training_program_id' => $program->id]);

        $response = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/training-programs');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $program->id);
        $this->assertSame(3, $row['attendee_count']);
    }

    public function test_training_program_show_reports_real_attendee_count(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $program = TrainingProgram::factory()->create();
        TrainingHistory::factory()->count(2)->create(['training_program_id' => $program->id]);

        $response = $this->actingAs($officer, 'sanctum')->getJson("/api/v1/training-programs/{$program->id}");

        $response->assertOk()->assertJsonPath('data.attendee_count', 2);
    }

    public function test_trainee_list_reports_real_training_counts(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create();
        TrainingHistory::factory()->count(4)->create(['trainee_id' => $trainee->id]);

        $response = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/trainees');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $trainee->id);
        $this->assertSame(4, $row['training_count']);
    }
}
