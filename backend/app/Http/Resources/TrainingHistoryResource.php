<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trainee_id' => $this->trainee_id,
            'training_program_id' => $this->training_program_id,
            'training_program' => new TrainingProgramResource($this->whenLoaded('trainingProgram')),
            'chain_level' => $this->chain_level,
            'date_attended' => $this->date_attended?->toDateString(),
            'remarks' => $this->remarks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
