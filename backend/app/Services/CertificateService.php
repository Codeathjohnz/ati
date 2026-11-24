<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\TraineeProfile;
use App\Models\TraineeTestAttempt;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramTest;
use App\Models\TrainingRegistration;
use Illuminate\Support\Facades\DB;

/**
 * Issues a Certificate of Completion for a specific training program once a
 * trainee has completed every step of it: registered, attended, passed its
 * pre-test and post-test (only the ones the officer actually published —
 * a program with no test configured has nothing to pass), and submitted
 * its Resource Person evaluation. A trainee can earn one certificate per
 * program (enforced by the table's unique (trainee_id, training_program_id));
 * re-checking an already-issued one is a no-op that returns the existing
 * certificate.
 */
class CertificateService
{
    /** Returns the certificate for this trainee+program, issuing it now if they just completed every step — or null if not yet eligible. */
    public function ensureIssued(TraineeProfile $trainee, TrainingProgram $program): ?Certificate
    {
        $existing = Certificate::where('trainee_id', $trainee->id)
            ->where('training_program_id', $program->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        if (! TrainingRegistration::where('trainee_id', $trainee->id)->where('training_program_id', $program->id)->exists()) {
            return null;
        }

        $history = TrainingHistory::where('trainee_id', $trainee->id)->where('training_program_id', $program->id)->first();
        if (! $history) {
            return null;
        }

        if (! $this->passedPublishedTest($history, TrainingProgramTest::TYPE_PRE)) {
            return null;
        }

        if (! $this->passedPublishedTest($history, TrainingProgramTest::TYPE_POST)) {
            return null;
        }

        if (! $history->rpEvaluation) {
            return null;
        }

        return DB::transaction(function () use ($trainee, $program, $history) {
            $chainLevels = collect($program->chain_levels)->map(fn ($level) => [
                'chain_level' => $level,
                'date_attended' => $history->date_attended?->toDateString(),
            ])->values();

            $certificate = Certificate::create([
                'trainee_id' => $trainee->id,
                'training_program_id' => $program->id,
                'certificate_number' => 'PENDING',
                'issued_at' => now(),
                'chain_levels' => $chainLevels,
            ]);

            $certificate->update([
                'certificate_number' => sprintf('ATI-CARAGA-%d-%06d', now()->year, $certificate->id),
            ]);

            return $certificate;
        });
    }

    /**
     * True if the program has no published test of this type (nothing to
     * require), or the trainee has a passing attempt against the published
     * one.
     */
    private function passedPublishedTest(TrainingHistory $history, string $type): bool
    {
        $test = TrainingProgramTest::where('training_program_id', $history->training_program_id)
            ->where('type', $type)
            ->where('is_published', true)
            ->first();

        if (! $test) {
            return true;
        }

        return TraineeTestAttempt::where('training_history_id', $history->id)
            ->where('training_program_test_id', $test->id)
            ->where('passed', true)
            ->exists();
    }
}
