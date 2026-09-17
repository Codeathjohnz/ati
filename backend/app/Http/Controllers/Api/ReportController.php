<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentSubmission;
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
        $rows = TrainingHistory::with(['trainee', 'trainingProgram'])->orderByDesc('date_attended')->get();

        return $this->csv('attendance-report.csv', [
            'Trainee', 'Sex', 'Province', 'City/Municipality', 'Training Program', 'Chain Level', 'Date Attended',
        ], $rows->map(fn (TrainingHistory $h) => [
            $h->trainee?->full_name,
            $h->trainee?->sex,
            $h->trainee?->province,
            $h->trainee?->city_municipality,
            $h->trainingProgram?->name,
            $h->chain_level,
            optional($h->date_attended)->toDateString(),
        ]));
    }

    public function documentCompliance(Request $request, DocumentComplianceService $service): StreamedResponse
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

        return $this->csv('document-compliance-report.csv', [
            'Trainee', 'Province', 'City/Municipality', 'Trainings Attended', 'Overall Compliance Status',
        ], $rows);
    }

    public function competencyGaps(Request $request, CompetencyGapAnalysisService $service): StreamedResponse
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

        return $this->csv('competency-gap-report.csv', [
            'Trainee', 'Province', 'City/Municipality', 'Incomplete Chain Levels', 'Gap Count',
        ], $rows);
    }

    public function demographics(Request $request): StreamedResponse
    {
        $trainees = TraineeProfile::orderBy('last_name')->get();

        return $this->csv('trainee-demographics-report.csv', [
            'Full Name', 'Sex', 'Age', 'Province', 'City/Municipality', 'LGU Income Class',
            'Educational Level', 'Organization/Affiliation',
        ], $trainees->map(fn (TraineeProfile $t) => [
            $t->full_name, $t->sex, $t->age, $t->province, $t->city_municipality,
            $t->lgu_income_class, $t->educational_level, $t->organization_affiliation,
        ]));
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
}
