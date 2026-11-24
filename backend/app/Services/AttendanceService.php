<?php

namespace App\Services;

use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use Illuminate\Support\Facades\DB;

/**
 * Records a trainee's attendance in a training program — shared by the
 * officer's manual "Record Training Attendance" form and a trainee's own
 * QR-code check-in, so both paths seed the same document_submissions rows
 * and stay behaviorally identical regardless of who triggered them.
 */
class AttendanceService
{
    public function record(TraineeProfile $trainee, TrainingProgram $program, string $dateAttended, ?string $remarks, ?int $createdByUserId): TrainingHistory
    {
        return DB::transaction(function () use ($trainee, $program, $dateAttended, $remarks, $createdByUserId) {
            $history = TrainingHistory::create([
                'trainee_id' => $trainee->id,
                'training_program_id' => $program->id,
                'chain_levels' => $program->chain_levels,
                'date_attended' => $dateAttended,
                'remarks' => $remarks,
                'created_by' => $createdByUserId,
            ]);

            $requiredDocuments = RequiredDocument::where('is_active', true)->get();
            foreach ($requiredDocuments as $doc) {
                $history->documentSubmissions()->create([
                    'trainee_id' => $trainee->id,
                    'required_document_id' => $doc->id,
                    'submitted' => false,
                ]);
            }

            return $history;
        });
    }
}
