<?php

namespace Tests\Unit;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Services\CompetencyGapAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reproduces the Competency Gap Analysis Algorithm example from Table 5.0
 * of the capstone document (Chapter 3, Algorithm Design): a trainee who has
 * completed Production, Post-Harvest, and Consumption should have
 * Processing and Marketing flagged as gaps.
 */
class CompetencyGapAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_flags_incomplete_chain_levels_as_gaps(): void
    {
        $trainee = TraineeProfile::factory()->create();

        foreach (['Production', 'Post-Harvest', 'Consumption'] as $level) {
            $program = TrainingProgram::factory()->create(['chain_level' => $level]);
            TrainingHistory::factory()->create([
                'trainee_id' => $trainee->id,
                'training_program_id' => $program->id,
                'chain_level' => $level,
                'date_attended' => now(),
            ]);
        }

        $summary = (new CompetencyGapAnalysisService)->summarize($trainee);

        $this->assertEqualsCanonicalizing(['Processing', 'Marketing'], $summary['gaps']);
        $this->assertSame(2, $summary['gap_count']);
        $this->assertTrue($summary['has_gaps']);

        $statuses = collect($summary['chain_levels'])->pluck('status', 'chain_level');
        $this->assertSame('Complete', $statuses['Production']);
        $this->assertSame('Complete', $statuses['Post-Harvest']);
        $this->assertSame('Incomplete (Gap)', $statuses['Processing']);
        $this->assertSame('Incomplete (Gap)', $statuses['Marketing']);
        $this->assertSame('Complete', $statuses['Consumption']);
    }

    public function test_a_trainee_with_no_history_has_all_five_gaps(): void
    {
        $trainee = TraineeProfile::factory()->create();

        $summary = (new CompetencyGapAnalysisService)->summarize($trainee);

        $this->assertSame(5, $summary['gap_count']);
        $this->assertEqualsCanonicalizing(TrainingProgram::CHAIN_LEVELS, $summary['gaps']);
    }
}
