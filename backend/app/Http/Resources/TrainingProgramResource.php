<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'chain_levels' => $this->chain_levels,
            'venue' => $this->venue,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status,
            'is_posted' => (bool) $this->is_posted,
            'created_by' => $this->created_by,
            // Populated via withCount()/loadCount() as `training_history_count`
            // (a plain integer attribute) rather than the relation itself —
            // relationLoaded() would never be true here.
            'attendee_count' => $this->when(
                isset($this->training_history_count) || $this->relationLoaded('trainingHistory'),
                fn () => $this->training_history_count ?? $this->trainingHistory->count()
            ),
            'registration_count' => $this->when(
                isset($this->registrations_count) || $this->relationLoaded('registrations'),
                fn () => $this->registrations_count ?? $this->registrations->count()
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
