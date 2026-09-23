<?php

namespace App\Services;

use App\Models\TestQuestion;
use App\Models\TrainingProgramTest;
use Illuminate\Support\Collection;

/**
 * Auto-grades a trainee's Pre-Test/Post-Test submission against the
 * officer-set answer key.
 *
 * Multiple-choice compares the submitted option to `correct_answer` exactly.
 * Fill-in-the-blank compares trimmed, case-insensitive text — a known v1
 * simplification with no fuzzy/partial-credit matching, so answer keys
 * should list the expected wording exactly as trainees would type it.
 */
class TestGradingService
{
    /**
     * @param  array<int|string, string>  $answers  question_id => submitted answer
     * @return array{raw_score: int, max_score: int, percentage: int, passed: bool, graded: array}
     */
    public function grade(TrainingProgramTest $test, array $answers): array
    {
        $questions = $test->questions;

        $graded = $questions->map(function (TestQuestion $question) use ($answers) {
            $submitted = (string) ($answers[$question->id] ?? '');
            $correct = $this->isCorrect($question, $submitted);

            return [
                'question_id' => $question->id,
                'submitted_answer' => $submitted,
                'correct' => $correct,
                'points_awarded' => $correct ? $question->points : 0,
            ];
        });

        $rawScore = (int) $graded->sum('points_awarded');
        $maxScore = (int) $questions->sum('points');
        $percentage = $maxScore > 0 ? (int) round(($rawScore / $maxScore) * 100) : 0;

        return [
            'raw_score' => $rawScore,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'passed' => $percentage >= $test->passing_score,
            'graded' => $graded->all(),
        ];
    }

    private function isCorrect(TestQuestion $question, string $submitted): bool
    {
        if ($question->type === TestQuestion::TYPE_MULTIPLE_CHOICE) {
            return $submitted === $question->correct_answer;
        }

        // fill_blank: trimmed, case-insensitive exact match.
        return mb_strtolower(trim($submitted)) === mb_strtolower(trim($question->correct_answer));
    }

    /** @return Collection<int, TestQuestion> */
    public function questionsWithoutAnswers(TrainingProgramTest $test): Collection
    {
        return $test->questions->map(fn (TestQuestion $q) => (object) [
            'id' => $q->id,
            'type' => $q->type,
            'question_text' => $q->question_text,
            'options' => $q->options,
            'points' => $q->points,
        ]);
    }
}
