<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RpEvaluationResource;
use App\Models\RpEvaluation;
use App\Models\TrainingHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RpEvaluationController extends Controller
{
    private const LIKERT_RULES = ['required', 'integer', 'min:1', 'max:5'];

    /** Trainee: their own evaluation for this attendance, if they've submitted one. */
    public function show(Request $request, TrainingHistory $trainingHistory)
    {
        $this->authorizeOwnHistory($request, $trainingHistory);

        $evaluation = $trainingHistory->rpEvaluation;

        return response()->json(['data' => $evaluation ? new RpEvaluationResource($evaluation) : null]);
    }

    /** Trainee: submit the Resource Person evaluation for a training they attended. */
    public function store(Request $request, TrainingHistory $trainingHistory)
    {
        $this->authorizeOwnHistory($request, $trainingHistory);

        if ($trainingHistory->rpEvaluation) {
            abort(422, 'You have already submitted an evaluation for this training.');
        }

        $validated = $request->validate([
            'topic_ratings' => ['nullable', 'array'],
            'topic_ratings.*.topic' => ['required', 'string', 'max:200'],
            'topic_ratings.*.relevance' => ['required', Rule::in(['Very Relevant', 'Somewhat relevant', 'Not at all relevant'])],
            'topic_ratings.*.time_allocation' => ['required', Rule::in(['Just right', 'Need improvement'])],
            'topic_ratings.*.suggested_time' => ['nullable', 'string', 'max:200'],
            'topic_ratings.*.methodology' => ['required', Rule::in(['Effective', 'Not Effective'])],
            'topic_ratings.*.suggested_methodology' => ['nullable', 'string', 'max:200'],

            'clarity_objectives' => self::LIKERT_RULES,
            'topic_organization' => self::LIKERT_RULES,
            'clarity_presentation' => self::LIKERT_RULES,
            'instructional_aids_quality' => self::LIKERT_RULES,
            'teaching_ability' => self::LIKERT_RULES,
            'question_answering_ability' => self::LIKERT_RULES,
            'participant_interest' => self::LIKERT_RULES,
            'time_management' => self::LIKERT_RULES,
            'topic_ending' => self::LIKERT_RULES,
            'overall_satisfaction' => self::LIKERT_RULES,

            'liked_about_rp' => ['nullable', 'string'],
            'disliked_about_rp' => ['nullable', 'string'],
            'other_remarks' => ['nullable', 'string'],
        ]);

        $evaluation = RpEvaluation::create([
            ...$validated,
            'trainee_id' => $trainingHistory->trainee_id,
            'training_history_id' => $trainingHistory->id,
        ]);

        return new RpEvaluationResource($evaluation);
    }

    private function authorizeOwnHistory(Request $request, TrainingHistory $trainingHistory): void
    {
        $trainee = $request->user()->traineeProfile;
        abort_unless($trainee && $trainingHistory->trainee_id === $trainee->id, 403, 'Forbidden.');
    }
}
