<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingProgramRequest;
use App\Http\Resources\TrainingProgramResource;
use App\Models\ActivityLog;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;

class TrainingProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingProgram::query()->withCount('trainingHistory');

        if ($request->filled('chain_level')) {
            $query->where('chain_level', $request->string('chain_level'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->string('q').'%');
        }

        $trainings = $query->orderByDesc('start_date')->paginate(min((int) $request->input('per_page', 15), 100) ?: 15);

        return TrainingProgramResource::collection($trainings);
    }

    public function upcoming(Request $request)
    {
        $trainings = TrainingProgram::query()
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('start_date', '>=', now()->subDay())
            ->orderBy('start_date')
            ->get();

        return TrainingProgramResource::collection($trainings);
    }

    public function store(TrainingProgramRequest $request)
    {
        $training = TrainingProgram::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::record($request->user()->id, 'training_program.created', 'TrainingProgram', $training->id, "Created training program \"{$training->name}\".");

        return new TrainingProgramResource($training);
    }

    public function show(TrainingProgram $trainingProgram)
    {
        $trainingProgram->loadCount('trainingHistory');

        return new TrainingProgramResource($trainingProgram);
    }

    public function update(TrainingProgramRequest $request, TrainingProgram $trainingProgram)
    {
        $trainingProgram->update($request->validated());

        ActivityLog::record($request->user()->id, 'training_program.updated', 'TrainingProgram', $trainingProgram->id, "Updated training program \"{$trainingProgram->name}\".");

        return new TrainingProgramResource($trainingProgram);
    }

    public function destroy(Request $request, TrainingProgram $trainingProgram)
    {
        $name = $trainingProgram->name;
        $trainingProgram->delete();

        ActivityLog::record($request->user()->id, 'training_program.deleted', 'TrainingProgram', $trainingProgram->id, "Deleted training program \"{$name}\".");

        return response()->json(['message' => 'Training program deleted.']);
    }
}
