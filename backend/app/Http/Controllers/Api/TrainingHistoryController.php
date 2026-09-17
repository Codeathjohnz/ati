<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingHistoryRequest;
use App\Http\Resources\TrainingHistoryResource;
use App\Models\ActivityLog;
use App\Models\RequiredDocument;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingHistoryController extends Controller
{
    public function index(TraineeProfile $trainee)
    {
        $history = $trainee->trainingHistory()->with('trainingProgram')->orderByDesc('date_attended')->get();

        return TrainingHistoryResource::collection($history);
    }

    /**
     * Records a trainee's attendance in a training program (chain level is
     * copied from the program) and seeds a document_submissions row for every
     * active required document so the Document Status Tracker has something
     * to track from day one.
     */
    public function store(TrainingHistoryRequest $request, TraineeProfile $trainee)
    {
        $program = TrainingProgram::findOrFail($request->validated('training_program_id'));

        $history = DB::transaction(function () use ($request, $trainee, $program) {
            $history = TrainingHistory::create([
                'trainee_id' => $trainee->id,
                'training_program_id' => $program->id,
                'chain_level' => $program->chain_level,
                'date_attended' => $request->validated('date_attended'),
                'remarks' => $request->validated('remarks'),
                'created_by' => $request->user()->id,
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

        ActivityLog::record($request->user()->id, 'training_history.created', 'TraineeProfile', $trainee->id, "Recorded attendance of {$trainee->full_name} in \"{$program->name}\".");

        return new TrainingHistoryResource($history->load('trainingProgram'));
    }

    public function update(TrainingHistoryRequest $request, TrainingHistory $trainingHistory)
    {
        $trainingHistory->update([
            'date_attended' => $request->validated('date_attended'),
            'remarks' => $request->validated('remarks'),
        ]);

        ActivityLog::record($request->user()->id, 'training_history.updated', 'TraineeProfile', $trainingHistory->trainee_id, 'Updated a training history record.');

        return new TrainingHistoryResource($trainingHistory->load('trainingProgram'));
    }

    public function destroy(Request $request, TrainingHistory $trainingHistory)
    {
        $traineeId = $trainingHistory->trainee_id;
        $trainingHistory->documentSubmissions()->delete();
        $trainingHistory->delete();

        ActivityLog::record($request->user()->id, 'training_history.deleted', 'TraineeProfile', $traineeId, 'Removed a training history record.');

        return response()->json(['message' => 'Training history record deleted.']);
    }
}
