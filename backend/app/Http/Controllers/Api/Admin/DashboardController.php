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

        $trainingsByChainLevel = TrainingHistory::selectRaw('chain_level, COUNT(*) as total')
            ->groupBy('chain_level')
            ->pluck('total', 'chain_level');

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
        $completedByLevel = TrainingHistory::selectRaw('chain_level, COUNT(DISTINCT trainee_id) as total')
            ->groupBy('chain_level')
            ->pluck('total', 'chain_level');

        $competencyGapCoverage = collect(TrainingProgram::CHAIN_LEVELS)->map(function (string $level) use ($completedByLevel, $totalTrainees) {
            $completed = (int) ($completedByLevel[$level] ?? 0);

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
        ]);
    }
}
