<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DocumentSubmission;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Services\DocumentComplianceService;
use Illuminate\Http\Request;

class DocumentSubmissionController extends Controller
{
    /**
     * Document Status Tracker (REQ0010 / Objective 2): overall Yes/No per
     * document and Passed/Failed compliance, across every training the
     * trainee has attended.
     */
    public function forTrainee(TraineeProfile $trainee, DocumentComplianceService $service)
    {
        return response()->json($service->evaluateForTrainee($trainee));
    }

    public function forTrainingHistory(TrainingHistory $trainingHistory, DocumentComplianceService $service)
    {
        return response()->json($service->evaluateForTrainingHistory($trainingHistory));
    }

    /**
     * Toggle a single required document's Yes/No submission status for one
     * training attendance record.
     */
    public function update(Request $request, TrainingHistory $trainingHistory, int $requiredDocumentId)
    {
        $validated = $request->validate([
            'submitted' => ['required', 'boolean'],
            'date_submitted' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $submission = DocumentSubmission::updateOrCreate(
            [
                'trainee_id' => $trainingHistory->trainee_id,
                'training_history_id' => $trainingHistory->id,
                'required_document_id' => $requiredDocumentId,
            ],
            [
                'submitted' => $validated['submitted'],
                'date_submitted' => $validated['submitted'] ? ($validated['date_submitted'] ?? now()->toDateString()) : null,
                'remarks' => $validated['remarks'] ?? null,
                'updated_by' => $request->user()->id,
            ]
        );

        ActivityLog::record(
            $request->user()->id,
            'document_submission.updated',
            'TraineeProfile',
            $trainingHistory->trainee_id,
            'Marked document "'.$submission->requiredDocument?->name.'" as '.($validated['submitted'] ? 'submitted' : 'not submitted').'.'
        );

        return response()->json($submission->fresh());
    }
}
