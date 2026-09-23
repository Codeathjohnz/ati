<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
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

    public function test_trainees_by_province_is_a_plain_group_by_count(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        TraineeProfile::factory()->count(2)->create(['province' => 'Agusan del Norte']);
        TraineeProfile::factory()->create(['province' => 'Surigao del Sur']);

        $response = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/dashboard');

        $response->assertOk();
        $byProvince = $response->json('trainees_by_province');
        $this->assertSame(2, $byProvince['Agusan del Norte']);
        $this->assertSame(1, $byProvince['Surigao del Sur']);
    }

    public function test_province_completion_ranking_matches_the_all_five_chain_levels_rule_and_sorts_by_rate(): void
    {
        $officer = User::factory()->projectOfficer()->create();

        // Agusan del Norte: 1 of 2 trainees complete (all 5 chain levels) = 50%.
        $complete = TraineeProfile::factory()->create(['province' => 'Agusan del Norte']);
        foreach (TrainingProgram::CHAIN_LEVELS as $level) {
            TrainingHistory::factory()->create(['trainee_id' => $complete->id, 'chain_level' => $level]);
        }
        $incomplete = TraineeProfile::factory()->create(['province' => 'Agusan del Norte']);
        TrainingHistory::factory()->create(['trainee_id' => $incomplete->id, 'chain_level' => 'Production']);

        // Surigao del Sur: 0 of 1 complete = 0%.
        $noneComplete = TraineeProfile::factory()->create(['province' => 'Surigao del Sur']);
        TrainingHistory::factory()->create(['trainee_id' => $noneComplete->id, 'chain_level' => 'Production']);

        $response = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/dashboard');

        $response->assertOk();
        $ranking = collect($response->json('province_completion_ranking'));

        $agusan = $ranking->firstWhere('province', 'Agusan del Norte');
        $this->assertSame(2, $agusan['total_trainees']);
        $this->assertSame(1, $agusan['completed_trainees']);
        // JSON round-trips a whole-number float (50.0) as a bare int (50).
        $this->assertEquals(50.0, $agusan['completion_rate']);

        $surigao = $ranking->firstWhere('province', 'Surigao del Sur');
        $this->assertSame(0, $surigao['completed_trainees']);
        $this->assertEquals(0.0, $surigao['completion_rate']);

        // Sorted descending by completion_rate — Agusan del Norte (50%) ranks above Surigao del Sur (0%).
        $this->assertSame('Agusan del Norte', $ranking->first()['province']);
    }
}
