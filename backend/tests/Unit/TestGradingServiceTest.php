<?php

namespace Tests\Unit;

use App\Models\TrainingProgram;
use App\Models\TrainingProgramTest;
use App\Services\TestGradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestGradingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTest(int $passingScore = 70): TrainingProgramTest
    {
        $program = TrainingProgram::factory()->create();

        return TrainingProgramTest::create([
            'training_program_id' => $program->id,
            'type' => 'pre_test',
            'title' => 'Sample Pre-Test',
            'passing_score' => $passingScore,
        ]);
    }

    public function test_multiple_choice_grades_exact_match_only(): void
    {
        $test = $this->makeTest();
        $q = $test->questions()->create([
            'type' => 'multiple_choice',
            'question_text' => 'Which chain level covers growing crops?',
            'options' => ['Production', 'Marketing', 'Consumption'],
            'correct_answer' => 'Production',
            'points' => 1,
            'sort_order' => 0,
        ]);

        $result = (new TestGradingService)->grade($test->fresh(), [$q->id => 'Production']);
        $this->assertSame(1, $result['raw_score']);
        $this->assertTrue($result['passed']);

        $result = (new TestGradingService)->grade($test->fresh(), [$q->id => 'Marketing']);
        $this->assertSame(0, $result['raw_score']);
        $this->assertFalse($result['passed']);
    }

    public function test_fill_blank_is_case_insensitive_and_trims_whitespace(): void
    {
        $test = $this->makeTest();
        $q = $test->questions()->create([
            'type' => 'fill_blank',
            'question_text' => 'The five chain levels end with ___.',
            'correct_answer' => 'Consumption',
            'points' => 1,
            'sort_order' => 0,
        ]);

        $result = (new TestGradingService)->grade($test->fresh(), [$q->id => '  consumption  ']);
        $this->assertSame(1, $result['raw_score']);
        $this->assertTrue($result['passed']);
    }

    public function test_percentage_and_pass_boundary_uses_the_configured_passing_score(): void
    {
        $test = $this->makeTest(passingScore: 50);
        $q1 = $test->questions()->create(['type' => 'multiple_choice', 'question_text' => 'Q1', 'options' => ['A', 'B'], 'correct_answer' => 'A', 'points' => 1, 'sort_order' => 0]);
        $q2 = $test->questions()->create(['type' => 'multiple_choice', 'question_text' => 'Q2', 'options' => ['A', 'B'], 'correct_answer' => 'B', 'points' => 1, 'sort_order' => 1]);

        // 1 of 2 correct = 50% = exactly the passing score => should pass.
        $result = (new TestGradingService)->grade($test->fresh(), [$q1->id => 'A', $q2->id => 'A']);
        $this->assertSame(50, $result['percentage']);
        $this->assertTrue($result['passed']);

        // 0 of 2 correct = 0% => fails.
        $result = (new TestGradingService)->grade($test->fresh(), [$q1->id => 'B', $q2->id => 'A']);
        $this->assertSame(0, $result['percentage']);
        $this->assertFalse($result['passed']);
    }

    public function test_an_unanswered_question_counts_as_incorrect_not_an_error(): void
    {
        $test = $this->makeTest();
        $q = $test->questions()->create(['type' => 'fill_blank', 'question_text' => 'Q1', 'correct_answer' => 'answer', 'points' => 1, 'sort_order' => 0]);

        $result = (new TestGradingService)->grade($test->fresh(), []);
        $this->assertSame(0, $result['raw_score']);
        $this->assertFalse($result['passed']);
    }
}
