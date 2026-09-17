<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingProgram;

class MetaController extends Controller
{
    /**
     * Reference/lookup data used to populate dropdowns and the Advanced
     * Search filter panel on the front end. Nothing here is trainee data —
     * it is the controlled vocabulary the capstone document itself defines
     * (chain levels, document types) plus a seeded Caraga locations list.
     */
    public function index()
    {
        return response()->json([
            'chain_levels' => TrainingProgram::CHAIN_LEVELS,
            'educational_levels' => TraineeProfile::EDUCATIONAL_LEVELS,
            'income_classes' => Location::INCOME_CLASSES,
            'training_statuses' => TrainingProgram::STATUSES,
            'required_documents' => RequiredDocument::where('is_active', true)->orderBy('sort_order')->get(['id', 'code', 'name']),
            'provinces' => Location::query()->distinct()->orderBy('province')->pluck('province'),
        ]);
    }

    public function locations()
    {
        return response()->json(
            Location::query()->orderBy('province')->orderBy('city_municipality')->get()
        );
    }
}
