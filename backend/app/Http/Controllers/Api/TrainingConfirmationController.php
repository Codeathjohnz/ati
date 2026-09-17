<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingConfirmationResource;
use App\Models\ActivityLog;
use App\Models\TraineeProfile;
use App\Models\TrainingConfirmation;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;

class TrainingConfirmationController extends Controller
{
    /**
     * A trainee's confirmations for upcoming scheduled training activities
     * (Upcoming Training Confirmation feature). Auto-creates a pending
     * confirmation row for every upcoming training the trainee doesn't yet
     * have one for, so the trainee always sees the full upcoming list.
     */
    public function forTrainee(TraineeProfile $trainee)
    {
        $upcoming = TrainingProgram::whereIn('status', ['upcoming', 'ongoing'])
            ->where('start_date', '>=', now()->subDay())
            ->get();

        foreach ($upcoming as $program) {
            TrainingConfirmation::firstOrCreate([
                'trainee_id' => $trainee->id,
                'training_program_id' => $program->id,
            ], ['status' => TrainingConfirmation::STATUS_PENDING]);
        }

        $confirmations = TrainingConfirmation::with('trainingProgram')
            ->where('trainee_id', $trainee->id)
            ->whereHas('trainingProgram', fn ($q) => $q->whereIn('status', ['upcoming', 'ongoing']))
            ->orderBy('created_at')
            ->get();

        return TrainingConfirmationResource::collection($confirmations);
    }

    public function forTrainingProgram(TrainingProgram $trainingProgram)
    {
        $confirmations = TrainingConfirmation::with('trainee')
            ->where('training_program_id', $trainingProgram->id)
            ->get();

        return TrainingConfirmationResource::collection($confirmations);
    }

    public function respond(Request $request, TrainingConfirmation $confirmation)
    {
        $user = $request->user();
        if ($user->isTrainee() && $confirmation->trainee_id !== $user->traineeProfile?->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,declined'],
        ]);

        $confirmation->update([
            'status' => $validated['status'],
            'confirmed_at' => now(),
        ]);

        ActivityLog::record(
            $request->user()->id,
            'confirmation.responded',
            'TraineeProfile',
            $confirmation->trainee_id,
            'Training participation '.$validated['status'].'.'
        );

        return new TrainingConfirmationResource($confirmation->fresh('trainingProgram'));
    }
}
