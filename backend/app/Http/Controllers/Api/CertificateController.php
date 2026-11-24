<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\TraineeProfile;
use App\Models\TrainingProgram;
use App\Services\CertificateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    private function resolveOwnTrainee(Request $request): TraineeProfile
    {
        $trainee = $request->user()->traineeProfile;
        abort_unless($trainee, 404, 'No trainee profile linked to this account.');

        return $trainee;
    }

    /** Trainee: their own certificate for this program, issuing it now if they just became eligible. */
    public function mine(Request $request, TrainingProgram $trainingProgram, CertificateService $service)
    {
        return $this->respondWith($service->ensureIssued($this->resolveOwnTrainee($request), $trainingProgram));
    }

    /** Officer/Admin: a specific trainee's certificate for this program. */
    public function forTrainee(TraineeProfile $trainee, TrainingProgram $trainingProgram, CertificateService $service)
    {
        return $this->respondWith($service->ensureIssued($trainee, $trainingProgram));
    }

    public function downloadMine(Request $request, TrainingProgram $trainingProgram, CertificateService $service)
    {
        return $this->downloadFor($this->resolveOwnTrainee($request), $trainingProgram, $service);
    }

    public function download(TraineeProfile $trainee, TrainingProgram $trainingProgram, CertificateService $service)
    {
        return $this->downloadFor($trainee, $trainingProgram, $service);
    }

    /** Public — no auth. What a QR scan (or anyone with the certificate number) lands on. */
    public function verify(string $certificateNumber)
    {
        $certificate = Certificate::with(['trainee', 'trainingProgram'])->where('certificate_number', $certificateNumber)->first();

        if (! $certificate) {
            return response()->json(['valid' => false], 404);
        }

        return response()->json([
            'valid' => true,
            'certificate_number' => $certificate->certificate_number,
            'trainee_name' => $certificate->trainee->full_name,
            'training_program_name' => $certificate->trainingProgram?->name,
            'issued_at' => $certificate->issued_at->toDateString(),
            'chain_levels' => $certificate->chain_levels,
        ]);
    }

    private function respondWith(?Certificate $certificate)
    {
        if (! $certificate) {
            return response()->json(['message' => 'Not every step of this training is complete yet.'], 404);
        }

        return response()->json($certificate);
    }

    private function downloadFor(TraineeProfile $trainee, TrainingProgram $trainingProgram, CertificateService $service)
    {
        $certificate = $service->ensureIssued($trainee, $trainingProgram);
        abort_unless($certificate, 404, 'Not every step of this training is complete yet.');

        $verifyUrl = rtrim(config('app.frontend_url'), '/')."/verify/{$certificate->certificate_number}";

        $qr = (new Builder(
            writer: new PngWriter(),
            data: $verifyUrl,
            size: 220,
            margin: 8,
        ))->build();

        $pdf = Pdf::loadView('certificates.certificate', [
            'trainee' => $trainee,
            'trainingProgram' => $trainingProgram,
            'certificate' => $certificate,
            'qrDataUri' => $qr->getDataUri(),
            'verifyUrl' => $verifyUrl,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("certificate-{$certificate->certificate_number}.pdf");
    }
}
