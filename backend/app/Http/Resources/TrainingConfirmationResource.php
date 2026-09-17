<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingConfirmationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trainee_id' => $this->trainee_id,
            'training_program_id' => $this->training_program_id,
            'status' => $this->status,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'training_program' => new TrainingProgramResource($this->whenLoaded('trainingProgram')),
            'trainee' => new TraineeProfileResource($this->whenLoaded('trainee')),
        ];
    }
}
