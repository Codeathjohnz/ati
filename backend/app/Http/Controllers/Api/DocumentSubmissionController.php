<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DocumentSubmission;
use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Services\DocumentComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentSubmissionController extends Controller
{
    private const DISK = 'trainee_documents';

    /**
     * Document Status Tracker (REQ0010 / Objective 2): overall Yes/No per
     * document and Passed/Incomplete compliance, across every training the
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
     * Officer review of one required document for one training attendance.
     * Either an explicit review decision (`review_status`), or the original
     * manual "mark as submitted" toggle for paperwork an officer handles
     * outside the system entirely — that path counts as the officer
     * attesting it themselves, so it goes straight to 'approved'.
     */
    public function update(Request $request, TrainingHistory $trainingHistory, int $requiredDocumentId)
    {
        $validated = $request->validate([
            'submitted' => ['sometimes', 'boolean'],
            'review_status' => ['sometimes', Rule::in([
                DocumentSubmission::REVIEW_PENDING,
                DocumentSubmission::REVIEW_APPROVED,
                DocumentSubmission::REVIEW_NEEDS_REVISION,
            ])],
            'date_submitted' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        if (! array_key_exists('submitted', $validated) && ! array_key_exists('review_status', $validated)) {
            abort(422, 'Provide either "submitted" or "review_status".');
        }

        if (array_key_exists('review_status', $validated)) {
            $reviewStatus = $validated['review_status'];
            $submitted = $reviewStatus !== DocumentSubmission::REVIEW_PENDING;
        } else {
            $submitted = $validated['submitted'];
            $reviewStatus = $submitted ? DocumentSubmission::REVIEW_APPROVED : DocumentSubmission::REVIEW_PENDING;
        }

        $submission = DocumentSubmission::updateOrCreate(
            [
                'trainee_id' => $trainingHistory->trainee_id,
                'training_history_id' => $trainingHistory->id,
                'required_document_id' => $requiredDocumentId,
            ],
            [
                'submitted' => $submitted,
                'review_status' => $reviewStatus,
                'date_submitted' => $submitted ? ($validated['date_submitted'] ?? now()->toDateString()) : null,
                'remarks' => $validated['remarks'] ?? null,
                'updated_by' => $request->user()->id,
            ]
        );

        ActivityLog::record(
            $request->user()->id,
            'document_submission.updated',
            'TraineeProfile',
            $trainingHistory->trainee_id,
            'Set document "'.$submission->requiredDocument?->name.'" to '.$reviewStatus.'.'
        );

        return response()->json($submission->fresh());
    }

    /**
     * A trainee uploads their own copy of a required document (or a photo of
     * a hardcopy pre/post-test). Always resolves the training history against
     * the caller's own trainee profile — never a client-supplied trainee id.
     */
    public function uploadAsTrainee(Request $request, TrainingHistory $trainingHistory, int $requiredDocumentId)
    {
        $trainee = $request->user()->traineeProfile;
        abort_unless($trainee, 404, 'No trainee profile linked to this account.');
        abort_unless($trainingHistory->trainee_id === $trainee->id, 403, 'This training record does not belong to you.');

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
        ]);

        $requiredDocument = RequiredDocument::findOrFail($requiredDocumentId);

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $validated['file'];
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $path = "{$trainee->id}/{$trainingHistory->id}/{$requiredDocument->id}.{$extension}";

        Storage::disk(self::DISK)->put($path, file_get_contents($file->getRealPath()));

        $submission = DocumentSubmission::updateOrCreate(
            [
                'trainee_id' => $trainee->id,
                'training_history_id' => $trainingHistory->id,
                'required_document_id' => $requiredDocument->id,
            ],
            [
                'submitted' => true,
                'review_status' => DocumentSubmission::REVIEW_PENDING,
                'date_submitted' => now()->toDateString(),
                'attachment_path' => $path,
                'attachment_original_name' => $file->getClientOriginalName(),
            ]
        );

        ActivityLog::record(
            $request->user()->id,
            'document_submission.uploaded',
            'TraineeProfile',
            $trainee->id,
            'Uploaded "'.$requiredDocument->name.'" for review.'
        );

        return response()->json($submission->fresh());
    }

    /**
     * Stream a document's attachment back down. Allowed for the owning
     * trainee (self-service route) or any admin/project officer (management
     * route) — never anyone else, regardless of which route reached here.
     */
    public function download(Request $request, TrainingHistory $trainingHistory, int $requiredDocumentId): StreamedResponse
    {
        $user = $request->user();
        $isOwner = $user->traineeProfile && $trainingHistory->trainee_id === $user->traineeProfile->id;
        $isStaff = $user->isAdmin() || $user->isProjectOfficer();
        abort_unless($isOwner || $isStaff, 403);

        $submission = DocumentSubmission::query()
            ->where('training_history_id', $trainingHistory->id)
            ->where('required_document_id', $requiredDocumentId)
            ->firstOrFail();

        abort_unless($submission->attachment_path, 404, 'No attachment on file for this document.');
        abort_unless(Storage::disk(self::DISK)->exists($submission->attachment_path), 404, 'Attachment file is missing.');

        return Storage::disk(self::DISK)->download(
            $submission->attachment_path,
            $submission->attachment_original_name ?? basename($submission->attachment_path)
        );
    }
}
