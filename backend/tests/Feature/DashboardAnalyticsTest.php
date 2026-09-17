<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the dashboard's institution-wide analytics: gap
 * coverage per chain level should mirror CompetencyGapAnalysisService's own
 * per-trainee rule, and the attendance trend should always return exactly
 * six zero-filled months regardless of how sparse the real data is.
 */
class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_competency_gap_coverage_matches_the_per_trainee_rule(): void
    {
        $officer = User::factory()->projectOfficer()->create();

        $completed = TraineeProfile::factory()->create();
        TrainingHistory::factory()->create(['trainee_id' => $completed->id, 'chain_level' => 'Production']);

        $missing = TraineeProfile::factory()->create();

        $response = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/dashboard');

        $response->assertOk();
        $coverage = collect($response->json('competency_gap_coverage'))->keyBy('chain_level');

        $this->assertSame(1, $coverage['Production']['completed']);
        $this->assertSame(1, $coverage['Production']['gap']); // the "missing" trainee
        $this->assertSame(0, $coverage['Marketing']['completed']);
        $this->assertSame(2, $coverage['Marketing']['gap']); // neither trainee has it
    }

    public function test_attendance_trend_always_returns_six_zero_filled_months(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create();
        TrainingHistory::factory()->create(['trainee_id' => $trainee->id, 'date_attended' => now()->toDateString()]);

        $response = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/dashboard');

        $response->assertOk();
        $trend = $response->json('attendance_trend');

        $this->assertCount(6, $trend);
        $this->assertSame(now()->format('M Y'), $trend[5]['month']);
        $this->assertSame(1, $trend[5]['total']);
        $this->assertSame(0, $trend[0]['total']);
    }
}
