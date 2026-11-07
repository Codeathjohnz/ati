<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\TraineeProfile;
use App\Models\TrainingProgram;
use Illuminate\Support\Facades\DB;

/**
 * Issues a Certificate of Completion the moment a trainee has no remaining
 * competency gaps — i.e. a training_history row for all 5 chain levels. A
 * trainee is only ever issued one certificate, ever (enforced by the
 * table's unique trainee_id); re-checking an already-issued trainee is a
 * no-op that just returns their existing certificate.
 */
class CertificateService
{
    public function __construct(private CompetencyGapAnalysisService $gaps) {}

    /** Returns the trainee's certificate, issuing it now if they just became eligible — or null if they still have gaps. */
    public function ensureIssued(TraineeProfile $trainee): ?Certificate
    {
        $existing = Certificate::where('trainee_id', $trainee->id)->first();
        if ($existing) {
            return $existing;
        }

        $summary = $this->gaps->summarize($trainee);
        if ($summary['has_gaps']) {
            return null;
        }

        return DB::transaction(function () use ($trainee) {
            // Earliest attendance per chain level — the date each requirement was actually met.
            $levelDates = $trainee->trainingHistory()
                ->orderBy('date_attended')
                ->get(['chain_level', 'date_attended'])
                ->unique('chain_level')
                ->sortBy(fn ($h) => array_search($h->chain_level, TrainingProgram::CHAIN_LEVELS, true))
                ->values()
                ->map(fn ($h) => [
                    'chain_level' => $h->chain_level,
                    'date_attended' => $h->date_attended?->toDateString(),
                ]);

            $certificate = Certificate::create([
                'trainee_id' => $trainee->id,
                'certificate_number' => 'PENDING',
                'issued_at' => now(),
                'chain_levels' => $levelDates,
            ]);

            $certificate->update([
                'certificate_number' => sprintf('ATI-CARAGA-%d-%06d', now()->year, $certificate->id),
            ]);

            return $certificate;
        });
    }
}
