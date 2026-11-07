<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\TraineeProfile;
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

    /** Trainee: their own certificate, issuing it now if they just became eligible. */
    public function mine(Request $request, CertificateService $service)
    {
        return $this->respondWith($service->ensureIssued($this->resolveOwnTrainee($request)));
    }

    /** Officer/Admin: a specific trainee's certificate. */
    public function forTrainee(TraineeProfile $trainee, CertificateService $service)
    {
        return $this->respondWith($service->ensureIssued($trainee));
    }

    public function downloadMine(Request $request, CertificateService $service)
    {
        return $this->downloadFor($this->resolveOwnTrainee($request), $service);
    }

    public function download(TraineeProfile $trainee, CertificateService $service)
    {
        return $this->downloadFor($trainee, $service);
    }

    /** Public — no auth. What a QR scan (or anyone with the certificate number) lands on. */
    public function verify(string $certificateNumber)
    {
        $certificate = Certificate::with('trainee')->where('certificate_number', $certificateNumber)->first();

        if (! $certificate) {
            return response()->json(['valid' => false], 404);
        }

        return response()->json([
            'valid' => true,
            'certificate_number' => $certificate->certificate_number,
            'trainee_name' => $certificate->trainee->full_name,
            'issued_at' => $certificate->issued_at->toDateString(),
            'chain_levels' => $certificate->chain_levels,
        ]);
    }

    private function respondWith(?Certificate $certificate)
    {
        if (! $certificate) {
            return response()->json(['message' => 'Not all chain levels are complete yet.'], 404);
        }

        return response()->json($certificate);
    }

    private function downloadFor(TraineeProfile $trainee, CertificateService $service)
    {
        $certificate = $service->ensureIssued($trainee);
        abort_unless($certificate, 404, 'Not all chain levels are complete yet.');

        $verifyUrl = rtrim(config('app.frontend_url'), '/')."/verify/{$certificate->certificate_number}";

        $qr = (new Builder(
            writer: new PngWriter(),
            data: $verifyUrl,
            size: 220,
            margin: 8,
        ))->build();

        $pdf = Pdf::loadView('certificates.certificate', [
            'trainee' => $trainee,
            'certificate' => $certificate,
            'qrDataUri' => $qr->getDataUri(),
            'verifyUrl' => $verifyUrl,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("certificate-{$certificate->certificate_number}.pdf");
    }
}
