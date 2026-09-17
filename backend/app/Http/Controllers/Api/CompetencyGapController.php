<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TraineeProfileResource;
use App\Models\TraineeProfile;
use App\Services\CompetencyGapAnalysisService;
use Illuminate\Http\Request;

class CompetencyGapController extends Controller
{
    public function forTrainee(TraineeProfile $trainee, CompetencyGapAnalysisService $service)
    {
        return response()->json($service->summarize($trainee));
    }

    /**
     * Bulk view (Objective 3 / Project Officer Module) for coordinators to find
     * trainees who have not yet completed a given chain level, optionally
     * filtered by the same demographic criteria as Advanced Search.
     */
    public function index(Request $request, CompetencyGapAnalysisService $service)
    {
        $query = TraineeProfile::query()->with('trainingHistory');

        if ($request->filled('province')) {
            $query->where('province', $request->string('province'));
        }
        if ($request->filled('city_municipality')) {
            $query->where('city_municipality', $request->string('city_municipality'));
        }
        if ($request->filled('sex')) {
            $query->where('sex', $request->string('sex'));
        }

        $chainLevelFilter = $request->string('chain_level')->toString();

        $trainees = $query->orderBy('last_name')->paginate(min((int) $request->input('per_page', 15), 100) ?: 15);

        $rows = collect($trainees->items())->map(function (TraineeProfile $trainee) use ($service) {
            $summary = $service->summarize($trainee);

            return [
                'trainee' => new TraineeProfileResource($trainee),
                'gap_count' => $summary['gap_count'],
                'gaps' => $summary['gaps'],
            ];
        });

        if ($chainLevelFilter) {
            $rows = $rows->filter(fn ($row) => in_array($chainLevelFilter, $row['gaps'], true))->values();
        }

        // Always the same shape, whether or not chain_level narrowed the page's
        // rows further — the front end relies on meta.current_page/last_page/total
        // being present either way.
        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $trainees->currentPage(),
                'last_page' => $trainees->lastPage(),
                'total' => $trainees->total(),
            ],
        ]);
    }
}
