<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TraineeProfileResource;
use App\Http\Resources\TrainingConfirmationResource;
use App\Http\Resources\TrainingHistoryResource;
use App\Models\ActivityLog;
use App\Models\TraineeProfile;
use App\Models\TrainingConfirmation;
use App\Models\TrainingProgram;
use App\Services\CompetencyGapAnalysisService;
use App\Services\DocumentComplianceService;
use Illuminate\Http\Request;

/**
 * Trainee Module (Ch.1 Scope): every endpoint here resolves the trainee
 * profile from the authenticated user's own account rather than trusting a
 * client-supplied id, so a trainee can never read another trainee's records.
 */
class TraineeSelfController extends Controller
{
    private function resolveTrainee(Request $request): TraineeProfile
    {
        $trainee = $request->user()->traineeProfile;
        abort_unless($trainee, 404, 'No trainee profile linked to this account.');

        return $trainee;
    }

    public function profile(Request $request)
    {
        $trainee = $this->resolveTrainee($request)->load('trainingHistory.trainingProgram');

        return new TraineeProfileResource($trainee);
    }

    public function documentCompliance(Request $request, DocumentComplianceService $service)
    {
        return response()->json($service->evaluateForTrainee($this->resolveTrainee($request)));
    }

    public function competencyGaps(Request $request, CompetencyGapAnalysisService $service)
    {
        return response()->json($service->summarize($this->resolveTrainee($request)));
    }

    public function confirmations(Request $request)
    {
        $trainee = $this->resolveTrainee($request);

        $upcoming = TrainingProgram::whereIn('status', ['upcoming', 'ongoing'])
            ->where('start_date', '>=', now()->subDay())
            ->get();

        foreach ($upcoming as $program) {
            TrainingConfirmation::firstOrCreate([
                'trainee_id' => $trainee->id,
                'training_program_id' => $program->id,
            ], ['status' => TrainingConfirmation::STATUS_PENDING]);
        }

        $confirmations = TrainingConfirmation::with('trainingProgram')
            ->where('trainee_id', $trainee->id)
            ->whereHas('trainingProgram', fn ($q) => $q->whereIn('status', ['upcoming', 'ongoing']))
            ->orderBy('created_at')
            ->get();

        return TrainingConfirmationResource::collection($confirmations);
    }

    public function activityHistory(
        Request $request,
        DocumentComplianceService $documents,
        CompetencyGapAnalysisService $gaps
    ) {
        $trainee = $this->resolveTrainee($request)->load(['trainingHistory.trainingProgram', 'confirmations.trainingProgram']);

        $logs = ActivityLog::query()
            ->where('subject_type', 'TraineeProfile')
            ->where('subject_id', $trainee->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return response()->json([
            'trainee' => new TraineeProfileResource($trainee),
            'training_history' => TrainingHistoryResource::collection($trainee->trainingHistory),
            'document_compliance' => $documents->evaluateForTrainee($trainee),
            'competency_gaps' => $gaps->summarize($trainee),
            'confirmations' => TrainingConfirmationResource::collection($trainee->confirmations),
            'activity_log' => $logs,
        ]);
    }
}
