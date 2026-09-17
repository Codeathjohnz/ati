<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for a real bug found in the running app: GET
 * /competency-gaps returned pagination info as top-level keys when
 * unfiltered but under a "meta" object when a chain_level filter was
 * applied. The front end always reads response.meta.current_page, so the
 * unfiltered case crashed the whole page with "Cannot read properties of
 * undefined (reading 'current_page')". Both cases must return the same shape.
 */
class CompetencyGapBulkListTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_response_shape_is_identical_with_and_without_a_chain_level_filter(): void
    {
        $officer = User::factory()->projectOfficer()->create();
        $trainee = TraineeProfile::factory()->create();
        TrainingHistory::factory()->create(['trainee_id' => $trainee->id, 'chain_level' => 'Production']);

        $unfiltered = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/competency-gaps');
        $unfiltered->assertOk()->assertJsonStructure([
            'data' => [['trainee', 'gap_count', 'gaps']],
            'meta' => ['current_page', 'last_page', 'total'],
        ]);

        $filtered = $this->actingAs($officer, 'sanctum')->getJson('/api/v1/competency-gaps?chain_level=Marketing');
        $filtered->assertOk()->assertJsonStructure([
            'data' => [['trainee', 'gap_count', 'gaps']],
            'meta' => ['current_page', 'last_page', 'total'],
        ]);

        // The trainee has a Production record only, so filtering to a gap in
        // "Marketing" must still surface them (Marketing is one of their gaps).
        $this->assertCount(1, $filtered->json('data'));
        $this->assertContains('Marketing', $filtered->json('data.0.gaps'));
    }
}
