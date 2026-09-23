<?php

namespace Tests\Feature;

use App\Models\DocumentSubmission;
use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    private function traineeWithHistory(): array
    {
        $user = User::factory()->trainee()->create();
        $trainee = TraineeProfile::factory()->create(['user_id' => $user->id]);
        $history = TrainingHistory::factory()->create(['trainee_id' => $trainee->id]);

        return [$user, $trainee, $history];
    }

    public function test_a_trainee_can_upload_a_document_for_their_own_training(): void
    {
        Storage::fake('trainee_documents');
        [$user, $trainee, $history] = $this->traineeWithHistory();
        $doc = RequiredDocument::factory()->create(['code' => 're_entry_plan']);

        $response = $this->actingAs($user, 'sanctum')->postJson(
            "/api/v1/trainee/me/training-history/{$history->id}/documents/{$doc->id}/upload",
            ['file' => UploadedFile::fake()->create('reentry.pdf', 500, 'application/pdf')]
        );

        $response->assertOk();
        $submission = DocumentSubmission::where('trainee_id', $trainee->id)->where('required_document_id', $doc->id)->firstOrFail();
        $this->assertTrue($submission->submitted);
        $this->assertSame('pending', $submission->review_status);
        $this->assertNotNull($submission->attachment_path);
        Storage::disk('trainee_documents')->assertExists($submission->attachment_path);
    }

    public function test_a_trainee_cannot_upload_against_another_trainees_training_history(): void
    {
        Storage::fake('trainee_documents');
        [$user] = $this->traineeWithHistory();
        [, , $otherHistory] = $this->traineeWithHistory();
        $doc = RequiredDocument::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson(
            "/api/v1/trainee/me/training-history/{$otherHistory->id}/documents/{$doc->id}/upload",
            ['file' => UploadedFile::fake()->create('sneaky.pdf', 100, 'application/pdf')]
        );

        $response->assertStatus(403);
    }

    public function test_an_unsupported_file_type_is_rejected(): void
    {
        Storage::fake('trainee_documents');
        [$user, , $history] = $this->traineeWithHistory();
        $doc = RequiredDocument::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson(
            "/api/v1/trainee/me/training-history/{$history->id}/documents/{$doc->id}/upload",
            ['file' => UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream')]
        );

        $response->assertStatus(422)->assertJsonValidationErrors('file');
    }

    public function test_officer_can_approve_a_submitted_document(): void
    {
        Storage::fake('trainee_documents');
        $officer = User::factory()->projectOfficer()->create();
        [$user, $trainee, $history] = $this->traineeWithHistory();
        $doc = RequiredDocument::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson(
            "/api/v1/trainee/me/training-history/{$history->id}/documents/{$doc->id}/upload",
            ['file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')]
        )->assertOk();

        $response = $this->actingAs($officer, 'sanctum')->putJson(
            "/api/v1/training-history/{$history->id}/documents/{$doc->id}",
            ['review_status' => 'approved']
        );

        $response->assertOk();
        $this->assertSame('approved', DocumentSubmission::where('trainee_id', $trainee->id)->first()->review_status);
    }

    public function test_officer_can_send_a_document_back_for_revision_with_a_remark(): void
    {
        Storage::fake('trainee_documents');
        $officer = User::factory()->projectOfficer()->create();
        [$user, $trainee, $history] = $this->traineeWithHistory();
        $doc = RequiredDocument::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson(
            "/api/v1/trainee/me/training-history/{$history->id}/documents/{$doc->id}/upload",
            ['file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')]
        )->assertOk();

        $this->actingAs($officer, 'sanctum')->putJson(
            "/api/v1/training-history/{$history->id}/documents/{$doc->id}",
            ['review_status' => 'needs_revision', 'remarks' => 'Signature page missing.']
        )->assertOk();

        $submission = DocumentSubmission::where('trainee_id', $trainee->id)->first();
        $this->assertSame('needs_revision', $submission->review_status);
        $this->assertSame('Signature page missing.', $submission->remarks);
    }

    public function test_only_the_owning_trainee_or_staff_can_download_an_attachment(): void
    {
        Storage::fake('trainee_documents');
        [$user, , $history] = $this->traineeWithHistory();
        [$otherUser] = $this->traineeWithHistory();
        $doc = RequiredDocument::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson(
            "/api/v1/trainee/me/training-history/{$history->id}/documents/{$doc->id}/upload",
            ['file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')]
        )->assertOk();

        $this->actingAs($user, 'sanctum')
            ->get("/api/v1/trainee/me/training-history/{$history->id}/documents/{$doc->id}/download")
            ->assertOk();

        $this->actingAs($otherUser, 'sanctum')
            ->get("/api/v1/trainee/me/training-history/{$history->id}/documents/{$doc->id}/download")
            ->assertStatus(403);
    }
}
