<?php

namespace App\Services;

use App\Models\DocumentSubmission;
use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;

/**
 * Implements the Document Status Computation Algorithm described in Chapter 3
 * (Algorithm Design): for each active required document, determine Yes/No
 * submission status, and compute an overall Passed/Incomplete compliance
 * status based on the completeness of all required submissions (see Table
 * 6.0 — the paper's own term "Failed" is surfaced to users as "Incomplete"
 * per client request, since it reads as a judgment on the trainee rather
 * than a neutral "still in progress" state; the underlying rule is
 * unchanged).
 *
 * A document only counts toward "Passed" once an officer has approved it
 * (`review_status === 'approved'`) — a trainee's own upload, or an
 * unreviewed submission, is not enough on its own.
 */
class DocumentComplianceService
{
    /**
     * Evaluate document compliance for a single training attendance record.
     *
     * @return array{training_history_id: int, documents: array, overall_status: string}
     */
    public function evaluateForTrainingHistory(TrainingHistory $history): array
    {
        $requiredDocuments = RequiredDocument::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $submissions = DocumentSubmission::query()
            ->where('training_history_id', $history->id)
            ->get()
            ->keyBy('required_document_id');

        $documents = $requiredDocuments->map(function (RequiredDocument $doc) use ($submissions) {
            $submission = $submissions->get($doc->id);

            return [
                'required_document_id' => $doc->id,
                'code' => $doc->code,
                'name' => $doc->name,
                'submitted' => (bool) ($submission?->submitted ?? false),
                'date_submitted' => $submission?->date_submitted?->toDateString(),
                'review_status' => $submission?->review_status ?? DocumentSubmission::REVIEW_PENDING,
                'remarks' => $submission?->remarks,
                'has_attachment' => (bool) $submission?->attachment_path,
                'attachment_name' => $submission?->attachment_original_name,
            ];
        })->values();

        $overallStatus = $documents->every(fn ($d) => $d['review_status'] === DocumentSubmission::REVIEW_APPROVED) && $requiredDocuments->isNotEmpty()
            ? 'Passed'
            : 'Incomplete';

        return [
            'training_history_id' => $history->id,
            'training_program_id' => $history->training_program_id,
            'documents' => $documents->all(),
            'overall_status' => $overallStatus,
        ];
    }

    /**
     * Evaluate document compliance across every training a trainee has attended.
     * Overall trainee status is Passed only when every attended training is Passed.
     *
     * @return array{per_training: array, overall_status: string}
     */
    public function evaluateForTrainee(TraineeProfile $trainee): array
    {
        $histories = $trainee->trainingHistory()->with('trainingProgram')->orderByDesc('date_attended')->get();

        if ($histories->isEmpty()) {
            return [
                'per_training' => [],
                'overall_status' => 'No Records',
            ];
        }

        $perTraining = $histories->map(function (TrainingHistory $history) {
            $evaluation = $this->evaluateForTrainingHistory($history);
            $evaluation['training_program_name'] = $history->trainingProgram?->name;
            $evaluation['date_attended'] = $history->date_attended?->toDateString();

            return $evaluation;
        });

        $overallStatus = $perTraining->every(fn ($e) => $e['overall_status'] === 'Passed') ? 'Passed' : 'Incomplete';

        return [
            'per_training' => $perTraining->values()->all(),
            'overall_status' => $overallStatus,
        ];
    }
}
