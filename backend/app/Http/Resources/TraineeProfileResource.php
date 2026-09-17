<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TraineeProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'full_name' => $this->full_name,
            'sex' => $this->sex,
            'birthdate' => $this->birthdate?->toDateString(),
            'age' => $this->age,
            'contact_number' => $this->contact_number,
            'email' => $this->email,
            'address_line' => $this->address_line,
            'barangay' => $this->barangay,
            'city_municipality' => $this->city_municipality,
            'province' => $this->province,
            'region' => $this->region,
            'lgu_income_class' => $this->lgu_income_class,
            'educational_level' => $this->educational_level,
            'organization_affiliation' => $this->organization_affiliation,
            'occupation' => $this->occupation,
            'year_last_attended' => $this->when(
                $this->relationLoaded('trainingHistory') || isset($this->year_last_attended),
                fn () => $this->year_last_attended ?? optional($this->trainingHistory->max('date_attended'))->format('Y')
            ),
            // Populated either via withCount('trainingHistory as training_count')
            // (a plain `training_count` attribute) or a full eager load of the
            // relation, depending on which controller action built the model.
            'training_count' => $this->when(
                isset($this->training_count) || $this->relationLoaded('trainingHistory'),
                fn () => $this->training_count ?? $this->trainingHistory->count()
            ),
            'has_login' => (bool) $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
