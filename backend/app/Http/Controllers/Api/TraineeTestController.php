<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DocumentSubmission;
use App\Models\RequiredDocument;
use App\Models\TraineeTestAttempt;
use App\Models\TrainingHistory;
use App\Models\TrainingProgramTest;
use App\Services\TestGradingService;
use Illuminate\Http\Request;

/**
 * Trainee-side: view a Pre-Test/Post-Test for one of their own training
 * attendances (without the answer key) and submit an attempt for immediate
 * auto-grading. Always resolves the trainee from the authenticated user, and
 * verifies the training history belongs to them, before touching anything.
 */
class TraineeTestController extends Controller
{
    private function resolveOwnedHistory(Request $request, TrainingHistory $trainingHistory): void
    {
        $trainee = $request->user()->traineeProfile;
        abort_unless($trainee, 404, 'No trainee profile linked to this account.');
        abort_unless($trainingHistory->trainee_id === $trainee->id, 403, 'This training record does not belong to you.');
    }

    private function validateType(string $type): void
    {
        abort_unless(in_array($type, [TrainingProgramTest::TYPE_PRE, TrainingProgramTest::TYPE_POST], true), 404);
    }

    public function show(Request $request, TrainingHistory $trainingHistory, string $type)
    {
        $this->validateType($type);
        $this->resolveOwnedHistory($request, $trainingHistory);

        $test = TrainingProgramTest::where('training_program_id', $trainingHistory->training_program_id)
            ->where('type', $type)
            ->with('questions')
            ->first();

        if (! $test) {
            return response()->json(['exists' => false]);
        }

        $trainee = $request->user()->traineeProfile;
        $latestAttempt = TraineeTestAttempt::where('training_program_test_id', $test->id)
            ->where('trainee_id', $trainee->id)
            ->where('training_history_id', $trainingHistory->id)
            ->latest('attempted_at')
            ->first();

        return response()->json([
            'exists' => true,
            'test' => [
                'id' => $test->id,
                'type' => $test->type,
                'title' => $test->title,
                'instructions' => $test->instructions,
                'passing_score' => $test->passing_score,
                // Never send the answer key to the trainee.
                'questions' => $test->questions->map(fn ($q) => [
                    'id' => $q->id,
                    'type' => $q->type,
                    'question_text' => $q->question_text,
                    'options' => $q->options,
                    'points' => $q->points,
                ]),
            ],
            'latest_attempt' => $latestAttempt,
        ]);
    }

    public function attempt(Request $request, TrainingHistory $trainingHistory, string $type, TestGradingService $grader)
    {
        $this->validateType($type);
        $this->resolveOwnedHistory($request, $trainingHistory);

        $test = TrainingProgramTest::where('training_program_id', $trainingHistory->training_program_id)
            ->where('type', $type)
            ->with('questions')
            ->first();
        abort_unless($test, 404, 'No test has been set up for this training yet.');

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'string'],
        ]);

        $trainee = $request->user()->traineeProfile;
        $result = $grader->grade($test, $validated['answers']);

        $attempt = TraineeTestAttempt::create([
            'training_program_test_id' => $test->id,
            'trainee_id' => $trainee->id,
            'training_history_id' => $trainingHistory->id,
            'answers' => $validated['answers'],
            'raw_score' => $result['raw_score'],
            'max_score' => $result['max_score'],
            'percentage' => $result['percentage'],
            'passed' => $result['passed'],
            'attempted_at' => now(),
        ]);

        // Reflect the graded result on the matching required-document row
        // (pre_test/post_test), the same one a hardcopy upload would touch.
        $requiredDocument = RequiredDocument::where('code', $type)->first();
        if ($requiredDocument) {
            DocumentSubmission::updateOrCreate(
                [
                    'trainee_id' => $trainee->id,
                    'training_history_id' => $trainingHistory->id,
                    'required_document_id' => $requiredDocument->id,
                ],
                [
                    'submitted' => true,
                    'review_status' => $result['passed'] ? DocumentSubmission::REVIEW_APPROVED : DocumentSubmission::REVIEW_NEEDS_REVISION,
                    'date_submitted' => now()->toDateString(),
                    'remarks' => "Online test — {$result['percentage']}% ({$result['raw_score']}/{$result['max_score']})",
                ]
            );
        }

        ActivityLog::record(
            $request->user()->id,
            'trainee_test.attempted',
            'TraineeProfile',
            $trainee->id,
            'Took the '.($type === TrainingProgramTest::TYPE_PRE ? 'Pre-Test' : 'Post-Test').' — scored '.$result['percentage'].'%.'
        );

        return response()->json([
            'attempt' => $attempt,
            'passing_score' => $test->passing_score,
        ]);
    }
}
