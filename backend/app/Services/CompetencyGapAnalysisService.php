<?php

namespace App\Services;

use App\Models\TraineeProfile;
use App\Models\TrainingProgram;

/**
 * Implements the Competency Gap Analysis Algorithm described in Chapter 3
 * (Algorithm Design) of the ATI-Caraga capstone document: a deterministic,
 * rule-based comparison of the fixed set of required chain levels against
 * the chain levels a trainee has actually completed, derived from their
 * Training History Records (see Table 5.0).
 */
class CompetencyGapAnalysisService
{
    /**
     * @return array<int, array{chain_level: string, required: bool, completed: bool, status: string}>
     */
    public function analyze(TraineeProfile $trainee): array
    {
        $completedLevels = $trainee->trainingHistory()
            ->distinct()
            ->pluck('chain_level')
            ->all();

        return collect(TrainingProgram::CHAIN_LEVELS)
            ->map(function (string $level) use ($completedLevels) {
                $completed = in_array($level, $completedLevels, true);

                return [
                    'chain_level' => $level,
                    'required' => true,
                    'completed' => $completed,
                    'status' => $completed ? 'Complete' : 'Incomplete (Gap)',
                ];
            })
            ->values()
            ->all();
    }

    public function summarize(TraineeProfile $trainee): array
    {
        $rows = $this->analyze($trainee);
        $gaps = collect($rows)->where('completed', false)->pluck('chain_level')->values()->all();

        return [
            'chain_levels' => $rows,
            'gap_count' => count($gaps),
            'gaps' => $gaps,
            'has_gaps' => count($gaps) > 0,
        ];
    }
}
