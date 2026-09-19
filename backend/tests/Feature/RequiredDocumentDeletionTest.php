<?php

namespace Tests\Feature;

use App\Models\DocumentSubmission;
use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deleting a required document cascades to every document_submissions row
 * for it (cascadeOnDelete in its migration) — across every trainee and
 * training it was ever tracked on. That's fine for a document that was
 * added by mistake and never actually used, but would silently erase real
 * compliance history for one that has been. Deletion must be refused once
 * any submission exists; Deactivate is the correct way to "remove" that one.
 */
class RequiredDocumentDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unused_required_document_can_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $document = RequiredDocument::factory()->create(['code' => 'unused_doc']);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/admin/required-documents/{$document->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('required_documents', ['id' => $document->id]);
    }

    public function test_a_required_document_with_submissions_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $document = RequiredDocument::factory()->create(['code' => 'used_doc']);
        $trainee = TraineeProfile::factory()->create();
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id]);
        DocumentSubmission::create([
            'trainee_id' => $trainee->id,
            'training_history_id' => $history->id,
            'required_document_id' => $document->id,
            'submitted' => true,
        ]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/admin/required-documents/{$document->id}");

        $response->assertStatus(422);
        $this->assertDatabaseHas('required_documents', ['id' => $document->id]);
        $this->assertDatabaseHas('document_submissions', ['required_document_id' => $document->id]);
    }
}
