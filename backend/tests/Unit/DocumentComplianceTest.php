<?php

namespace Tests\Unit;

use App\Models\DocumentSubmission;
use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Services\DocumentComplianceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reproduces the Document Status Computation Algorithm example from
 * Table 6.0 of the capstone document: four of five required documents
 * submitted (Evaluation Form missing) must compute an overall Failed status.
 */
class DocumentComplianceTest extends TestCase
{
    use RefreshDatabase;

    private function seedRequiredDocuments(): void
    {
        foreach ([
            ['code' => 're_entry_plan', 'name' => 'Re-Entry Plan', 'sort_order' => 1],
            ['code' => 'pre_test', 'name' => 'Pre-Test', 'sort_order' => 2],
            ['code' => 'post_test', 'name' => 'Post-Test', 'sort_order' => 3],
            ['code' => 'evaluation_form', 'name' => 'Evaluation Form', 'sort_order' => 4],
            ['code' => 'attendance_sheet', 'name' => 'Attendance Form', 'sort_order' => 5],
        ] as $doc) {
            RequiredDocument::create([...$doc, 'is_active' => true]);
        }
    }

    public function test_one_missing_document_fails_overall_compliance(): void
    {
        $this->seedRequiredDocuments();

        $trainee = TraineeProfile::factory()->create();
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id]);

        foreach (RequiredDocument::all() as $doc) {
            DocumentSubmission::create([
                'trainee_id' => $trainee->id,
                'training_history_id' => $history->id,
                'required_document_id' => $doc->id,
                'submitted' => $doc->code !== 'evaluation_form',
                'date_submitted' => $doc->code !== 'evaluation_form' ? now() : null,
            ]);
        }

        $result = (new DocumentComplianceService)->evaluateForTrainingHistory($history);

        $this->assertSame('Failed', $result['overall_status']);

        $byCode = collect($result['documents'])->keyBy('code');
        $this->assertTrue($byCode['re_entry_plan']['submitted']);
        $this->assertTrue($byCode['pre_test']['submitted']);
        $this->assertTrue($byCode['post_test']['submitted']);
        $this->assertFalse($byCode['evaluation_form']['submitted']);
        $this->assertTrue($byCode['attendance_sheet']['submitted']);
    }

    public function test_all_documents_submitted_passes_overall_compliance(): void
    {
        $this->seedRequiredDocuments();

        $trainee = TraineeProfile::factory()->create();
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id]);

        foreach (RequiredDocument::all() as $doc) {
            DocumentSubmission::create([
                'trainee_id' => $trainee->id,
                'training_history_id' => $history->id,
                'required_document_id' => $doc->id,
                'submitted' => true,
                'date_submitted' => now(),
            ]);
        }

        $result = (new DocumentComplianceService)->evaluateForTrainingHistory($history);

        $this->assertSame('Passed', $result['overall_status']);
    }

    public function test_trainee_with_no_training_history_has_no_records_status(): void
    {
        $trainee = TraineeProfile::factory()->create();

        $result = (new DocumentComplianceService)->evaluateForTrainee($trainee);

        $this->assertSame('No Records', $result['overall_status']);
        $this->assertEmpty($result['per_training']);
    }
}
