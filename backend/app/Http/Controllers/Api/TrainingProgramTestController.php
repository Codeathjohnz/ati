<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TestQuestion;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Officer-side authoring of a training program's Pre-Test / Post-Test:
 * title, instructions, passing score, and the full question set with its
 * answer key, saved in one payload since question counts are small.
 */
class TrainingProgramTestController extends Controller
{
    private function validateType(string $type): void
    {
        abort_unless(in_array($type, [TrainingProgramTest::TYPE_PRE, TrainingProgramTest::TYPE_POST], true), 404);
    }

    public function show(TrainingProgram $trainingProgram, string $type)
    {
        $this->validateType($type);

        $test = $trainingProgram->tests()->where('type', $type)->with('questions')->first();

        if (! $test) {
            return response()->json(['exists' => false]);
        }

        return response()->json(['exists' => true, 'test' => $test]);
    }

    public function upsert(Request $request, TrainingProgram $trainingProgram, string $type)
    {
        $this->validateType($type);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'instructions' => ['nullable', 'string'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.type' => ['required', Rule::in([TestQuestion::TYPE_MULTIPLE_CHOICE, TestQuestion::TYPE_FILL_BLANK])],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['string'],
            'questions.*.correct_answer' => ['required', 'string'],
            'questions.*.points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $test = DB::transaction(function () use ($request, $trainingProgram, $type, $validated) {
            $test = TrainingProgramTest::updateOrCreate(
                ['training_program_id' => $trainingProgram->id, 'type' => $type],
                [
                    'title' => $validated['title'],
                    'instructions' => $validated['instructions'] ?? null,
                    'passing_score' => $validated['passing_score'],
                    'created_by' => $request->user()->id,
                ]
            );

            // Question counts are small (a handful per test) — replace the
            // whole set rather than diffing individual rows.
            $test->questions()->delete();
            foreach ($validated['questions'] as $i => $q) {
                $test->questions()->create([
                    'type' => $q['type'],
                    'question_text' => $q['question_text'],
                    'options' => $q['type'] === TestQuestion::TYPE_MULTIPLE_CHOICE ? ($q['options'] ?? []) : null,
                    'correct_answer' => $q['correct_answer'],
                    'points' => $q['points'],
                    'sort_order' => $i,
                ]);
            }

            return $test;
        });

        ActivityLog::record(
            $request->user()->id,
            'training_program_test.saved',
            'TrainingProgram',
            $trainingProgram->id,
            'Saved the '.($type === TrainingProgramTest::TYPE_PRE ? 'Pre-Test' : 'Post-Test').' for "'.$trainingProgram->name.'".'
        );

        return response()->json($test->fresh('questions'));
    }

    public function destroy(Request $request, TrainingProgram $trainingProgram, string $type)
    {
        $this->validateType($type);

        $test = $trainingProgram->tests()->where('type', $type)->first();
        abort_unless($test, 404);
        $test->delete();

        ActivityLog::record(
            $request->user()->id,
            'training_program_test.deleted',
            'TrainingProgram',
            $trainingProgram->id,
            'Removed the '.($type === TrainingProgramTest::TYPE_PRE ? 'Pre-Test' : 'Post-Test').' for "'.$trainingProgram->name.'".'
        );

        return response()->json(['message' => 'Test removed.']);
    }
}
