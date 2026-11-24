<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingProgramResource;
use App\Http\Resources\TrainingRegistrationResource;
use App\Models\ActivityLog;
use App\Models\TraineeProfile;
use App\Models\TrainingProgram;
use App\Models\TrainingRegistration;
use Illuminate\Http\Request;

class TrainingRegistrationController extends Controller
{
    private function resolveOwnTrainee(Request $request): TraineeProfile
    {
        $trainee = $request->user()->traineeProfile;
        abort_unless($trainee, 404, 'No trainee profile linked to this account.');

        return $trainee;
    }

    /**
     * Trainee: every training an officer has posted as open for
     * registration, each flagged with whether this trainee has already
     * registered for it.
     */
    public function posted(Request $request)
    {
        $trainee = $this->resolveOwnTrainee($request);

        $registeredIds = TrainingRegistration::where('trainee_id', $trainee->id)->pluck('training_program_id');

        $trainings = TrainingProgram::where('is_posted', true)
            ->orderBy('start_date')
            ->get();

        return response()->json([
            'data' => $trainings->map(fn (TrainingProgram $program) => [
                ...(new TrainingProgramResource($program))->toArray($request),
                'is_registered' => $registeredIds->contains($program->id),
            ])->values(),
        ]);
    }

    /** Trainee: register interest in a posted training. */
    public function register(Request $request, TrainingProgram $trainingProgram)
    {
        abort_unless($trainingProgram->is_posted, 422, 'This training is not open for registration.');

        $trainee = $this->resolveOwnTrainee($request);

        $registration = TrainingRegistration::firstOrCreate(
            ['trainee_id' => $trainee->id, 'training_program_id' => $trainingProgram->id],
            ['registered_at' => now()]
        );

        ActivityLog::record(
            $request->user()->id,
            'training_registration.created',
            'TrainingProgram',
            $trainingProgram->id,
            "{$trainee->full_name} registered for \"{$trainingProgram->name}\"."
        );

        return new TrainingRegistrationResource($registration);
    }

    /** Trainee: withdraw a registration they no longer want. */
    public function withdraw(Request $request, TrainingProgram $trainingProgram)
    {
        $trainee = $this->resolveOwnTrainee($request);

        TrainingRegistration::where('trainee_id', $trainee->id)
            ->where('training_program_id', $trainingProgram->id)
            ->delete();

        ActivityLog::record(
            $request->user()->id,
            'training_registration.withdrawn',
            'TrainingProgram',
            $trainingProgram->id,
            "{$trainee->full_name} withdrew their registration for \"{$trainingProgram->name}\"."
        );

        return response()->json(['message' => 'Registration withdrawn.']);
    }

    /** Officer/Admin: who has registered for a given training. */
    public function forTrainingProgram(TrainingProgram $trainingProgram)
    {
        $registrations = TrainingRegistration::with('trainee')
            ->where('training_program_id', $trainingProgram->id)
            ->orderBy('registered_at')
            ->get();

        return TrainingRegistrationResource::collection($registrations);
    }
}
