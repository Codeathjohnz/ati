<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingHistoryRequest;
use App\Http\Resources\TrainingHistoryResource;
use App\Models\ActivityLog;
use App\Models\TraineeProfile;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

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
    public function store(TrainingHistoryRequest $request, TraineeProfile $trainee, AttendanceService $attendance)
    {
        $program = TrainingProgram::findOrFail($request->validated('training_program_id'));

        $history = $attendance->record(
            $trainee,
            $program,
            $request->validated('date_attended'),
            $request->validated('remarks'),
            $request->user()->id,
        );

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
