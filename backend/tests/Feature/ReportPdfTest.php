<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPdfTest extends TestCase
{
    use RefreshDatabase;

    private function asOfficer(): User
    {
        return User::factory()->create(['role' => User::ROLE_PROJECT_OFFICER]);
    }

    public function test_attendance_pdf_renders_inline(): void
    {
        $trainee = TraineeProfile::factory()->create();
        $program = TrainingProgram::factory()->create();
        TrainingHistory::factory()->create([
            'trainee_id' => $trainee->id,
            'training_program_id' => $program->id,
            'chain_level' => $program->chain_level,
        ]);

        $response = $this->actingAs($this->asOfficer())->get('/api/v1/reports/attendance/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_document_compliance_pdf_renders_inline(): void
    {
        TraineeProfile::factory()->create();

        $response = $this->actingAs($this->asOfficer())->get('/api/v1/reports/document-compliance/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_competency_gaps_pdf_renders_inline(): void
    {
        TraineeProfile::factory()->create();

        $response = $this->actingAs($this->asOfficer())->get('/api/v1/reports/competency-gaps/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_demographics_pdf_renders_inline(): void
    {
        TraineeProfile::factory()->create();

        $response = $this->actingAs($this->asOfficer())->get('/api/v1/reports/demographics/pdf');

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
