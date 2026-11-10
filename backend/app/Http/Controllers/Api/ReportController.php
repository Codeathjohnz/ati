<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Services\CompetencyGapAnalysisService;
use App\Services\DocumentComplianceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function attendance(Request $request): StreamedResponse
    {
        [$header, $rows] = $this->attendanceData();

        return $this->csv('attendance-report.csv', $header, $rows);
    }

    public function attendancePdf(Request $request)
    {
        [$header, $rows] = $this->attendanceData();

        return $this->pdf('Training Attendance Report', $header, $rows, 'attendance-report.pdf');
    }

    public function documentCompliance(Request $request, DocumentComplianceService $service): StreamedResponse
    {
        [$header, $rows] = $this->documentComplianceData($service);

        return $this->csv('document-compliance-report.csv', $header, $rows);
    }

    public function documentCompliancePdf(Request $request, DocumentComplianceService $service)
    {
        [$header, $rows] = $this->documentComplianceData($service);

        return $this->pdf('Document Compliance Report', $header, $rows, 'document-compliance-report.pdf');
    }

    public function competencyGaps(Request $request, CompetencyGapAnalysisService $service): StreamedResponse
    {
        [$header, $rows] = $this->competencyGapsData($service);

        return $this->csv('competency-gap-report.csv', $header, $rows);
    }

    public function competencyGapsPdf(Request $request, CompetencyGapAnalysisService $service)
    {
        [$header, $rows] = $this->competencyGapsData($service);

        return $this->pdf('Competency Gap Report', $header, $rows, 'competency-gap-report.pdf');
    }

    public function demographics(Request $request): StreamedResponse
    {
        [$header, $rows] = $this->demographicsData();

        return $this->csv('trainee-demographics-report.csv', $header, $rows);
    }

    public function demographicsPdf(Request $request)
    {
        [$header, $rows] = $this->demographicsData();

        return $this->pdf('Trainee Demographics Report', $header, $rows, 'trainee-demographics-report.pdf');
    }

    public function traineeActivityHistoryPdf(
        TraineeProfile $trainee,
        DocumentComplianceService $documents,
        CompetencyGapAnalysisService $gaps
    ) {
        $trainee->load(['trainingHistory.trainingProgram']);

        $pdf = Pdf::loadView('reports.activity-history', [
            'trainee' => $trainee,
            'documentCompliance' => $documents->evaluateForTrainee($trainee),
            'competencyGaps' => $gaps->summarize($trainee),
        ]);

        return $pdf->download('activity-history-'.$trainee->id.'.pdf');
    }

    /** @return array{0: array, 1: array} */
    private function attendanceData(): array
    {
        $rows = TrainingHistory::with(['trainee', 'trainingProgram'])->orderByDesc('date_attended')->get();

        return [
            ['Trainee', 'Sex', 'Province', 'City/Municipality', 'Training Program', 'Chain Level', 'Date Attended'],
            $rows->map(fn (TrainingHistory $h) => [
                $h->trainee?->full_name,
                $h->trainee?->sex,
                $h->trainee?->province,
                $h->trainee?->city_municipality,
                $h->trainingProgram?->name,
                $h->chain_level,
                optional($h->date_attended)->toDateString(),
            ])->all(),
        ];
    }

    /** @return array{0: array, 1: array} */
    private function documentComplianceData(DocumentComplianceService $service): array
    {
        $trainees = TraineeProfile::with('trainingHistory')->orderBy('last_name')->get();

        $rows = $trainees->map(function (TraineeProfile $trainee) use ($service) {
            $evaluation = $service->evaluateForTrainee($trainee);

            return [
                $trainee->full_name,
                $trainee->province,
                $trainee->city_municipality,
                count($evaluation['per_training']),
                $evaluation['overall_status'],
            ];
        });

        return [
            ['Trainee', 'Province', 'City/Municipality', 'Trainings Attended', 'Overall Compliance Status'],
            $rows->all(),
        ];
    }

    /** @return array{0: array, 1: array} */
    private function competencyGapsData(CompetencyGapAnalysisService $service): array
    {
        $trainees = TraineeProfile::with('trainingHistory')->orderBy('last_name')->get();

        $rows = $trainees->map(function (TraineeProfile $trainee) use ($service) {
            $summary = $service->summarize($trainee);

            return [
                $trainee->full_name,
                $trainee->province,
                $trainee->city_municipality,
                implode('; ', $summary['gaps']),
                $summary['gap_count'],
            ];
        });

        return [
            ['Trainee', 'Province', 'City/Municipality', 'Incomplete Chain Levels', 'Gap Count'],
            $rows->all(),
        ];
    }

    /** @return array{0: array, 1: array} */
    private function demographicsData(): array
    {
        $trainees = TraineeProfile::orderBy('last_name')->get();

        return [
            ['Full Name', 'Sex', 'Age', 'Province', 'City/Municipality', 'LGU Income Class', 'Educational Level', 'Organization/Affiliation'],
            $trainees->map(fn (TraineeProfile $t) => [
                $t->full_name, $t->sex, $t->age, $t->province, $t->city_municipality,
                $t->lgu_income_class, $t->educational_level, $t->organization_affiliation,
            ])->all(),
        ];
    }

    private function csv(string $filename, array $header, iterable $rows): StreamedResponse
    {
        $callback = function () use ($header, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $header);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Rendered inline (not force-downloaded) so opening it in a browser tab
     * gives View, Print, and Download for free via the native PDF viewer.
     */
    private function pdf(string $title, array $headers, array $rows, string $filename)
    {
        $pdf = Pdf::loadView('reports.generic-table', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream($filename);
    }
}
