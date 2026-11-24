<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentSubmission;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Real-time training attendance and document compliance statistics
     * (REQ004). Everything here is a live aggregate query against MySQL —
     * no hardcoded figures.
     */
    public function index()
    {
        $totalTrainees = TraineeProfile::count();
        $totalPrograms = TrainingProgram::count();
        $totalAttendances = TrainingHistory::count();

        $documentTotals = DocumentSubmission::selectRaw('submitted, COUNT(*) as total')
            ->groupBy('submitted')
            ->pluck('total', 'submitted');

        $submittedCount = (int) ($documentTotals[1] ?? 0);
        $missingCount = (int) ($documentTotals[0] ?? 0);
        $totalDocuments = $submittedCount + $missingCount;

        // Grouped in PHP, not via a raw GROUP BY on the chain_levels JSON
        // column (MySQL/SQLite JSON aggregation syntax differs) — a single
        // attendance can now cover several levels at once, and each one it
        // covers counts toward that level's total. One pass over every row
        // also builds, per trainee, the union of every level they've ever
        // been credited with — reused below for the per-level and
        // per-province "no gaps" aggregates instead of three separate
        // raw-SQL queries against a column that's no longer a plain scalar.
        $trainingsByChainLevel = collect(TrainingProgram::CHAIN_LEVELS)->mapWithKeys(fn ($level) => [$level => 0]);
        $traineeLevelSets = [];

        TrainingHistory::select('trainee_id', 'chain_levels')->get()->each(function ($row) use (&$trainingsByChainLevel, &$traineeLevelSets) {
            foreach ($row->chain_levels ?? [] as $level) {
                $trainingsByChainLevel[$level] = ($trainingsByChainLevel[$level] ?? 0) + 1;
                $traineeLevelSets[$row->trainee_id][$level] = true;
            }
        });

        $trainingsByStatus = TrainingProgram::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $traineesBySex = TraineeProfile::selectRaw('sex, COUNT(*) as total')->groupBy('sex')->pluck('total', 'sex');

        $usersByRole = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

        $recentTrainees = TraineeProfile::orderByDesc('created_at')->limit(5)->get(['id', 'first_name', 'last_name', 'created_at']);

        // Completed vs. gap per chain level, across the whole trainee population —
        // the same rule as CompetencyGapAnalysisService (a trainee "completes" a
        // level the moment they have one training_history row tagged with it),
        // aggregated institution-wide instead of per trainee.
        $competencyGapCoverage = collect(TrainingProgram::CHAIN_LEVELS)->map(function (string $level) use ($traineeLevelSets, $totalTrainees) {
            $completed = collect($traineeLevelSets)->filter(fn ($levels) => isset($levels[$level]))->count();

            return [
                'chain_level' => $level,
                'completed' => $completed,
                'gap' => max($totalTrainees - $completed, 0),
            ];
        })->values();

        // Training attendances recorded per month, most recent 6 months —
        // zero-filled so months with no activity still appear on the trend.
        // Grouped in PHP rather than via a MySQL-only DATE_FORMAT() so this
        // also works against the SQLite database the test suite runs on.
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $attendanceCounts = TrainingHistory::where('date_attended', '>=', $sixMonthsAgo->toDateString())
            ->pluck('date_attended')
            ->countBy(fn ($date) => $date->format('Y-m'));

        $attendanceTrend = collect(range(0, 5))->map(function (int $offset) use ($sixMonthsAgo, $attendanceCounts) {
            $month = $sixMonthsAgo->copy()->addMonths($offset);

            return [
                'month' => $month->format('M Y'),
                'total' => (int) ($attendanceCounts[$month->format('Y-m')] ?? 0),
            ];
        })->values();

        $traineesByProvince = TraineeProfile::selectRaw('province, COUNT(*) as total')
            ->groupBy('province')
            ->pluck('total', 'province');

        // Per province: how many trainees have a training_history row for
        // all 5 chain levels — the same "no gaps" rule as
        // CompetencyGapAnalysisService::summarize(), aggregated per province
        // instead of per trainee. Computed in PHP against $traineeLevelSets
        // (built above) rather than a raw correlated subquery, since
        // "distinct levels per trainee" is no longer a single-column count
        // once a row can carry several levels at once.
        $chainLevelCount = count(TrainingProgram::CHAIN_LEVELS);
        $provinceCompletionRanking = TraineeProfile::query()
            ->select('id', 'province')
            ->get()
            ->groupBy('province')
            ->map(function ($trainees, $province) use ($traineeLevelSets, $chainLevelCount) {
                $total = $trainees->count();
                $completed = $trainees->filter(fn ($t) => count($traineeLevelSets[$t->id] ?? []) === $chainLevelCount)->count();

                return [
                    'province' => $province,
                    'total_trainees' => $total,
                    'completed_trainees' => $completed,
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('completion_rate')
            ->values();

        return response()->json([
            'total_trainees' => $totalTrainees,
            'total_training_programs' => $totalPrograms,
            'total_attendances' => $totalAttendances,
            'document_compliance' => [
                'submitted' => $submittedCount,
                'missing' => $missingCount,
                'submission_rate' => $totalDocuments > 0 ? round(($submittedCount / $totalDocuments) * 100, 1) : 0,
            ],
            'trainings_by_chain_level' => $trainingsByChainLevel,
            'trainings_by_status' => $trainingsByStatus,
            'trainees_by_sex' => $traineesBySex,
            'users_by_role' => $usersByRole,
            'recent_trainees' => $recentTrainees,
            'competency_gap_coverage' => $competencyGapCoverage,
            'attendance_trend' => $attendanceTrend,
            'trainees_by_province' => $traineesByProvince,
            'province_completion_ranking' => $provinceCompletionRanking,
        ]);
    }
}
