<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RpEvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trainee_id' => $this->trainee_id,
            'training_history_id' => $this->training_history_id,
            'topic_ratings' => $this->topic_ratings,
            'clarity_objectives' => $this->clarity_objectives,
            'topic_organization' => $this->topic_organization,
            'clarity_presentation' => $this->clarity_presentation,
            'instructional_aids_quality' => $this->instructional_aids_quality,
            'teaching_ability' => $this->teaching_ability,
            'question_answering_ability' => $this->question_answering_ability,
            'participant_interest' => $this->participant_interest,
            'time_management' => $this->time_management,
            'topic_ending' => $this->topic_ending,
            'overall_satisfaction' => $this->overall_satisfaction,
            'liked_about_rp' => $this->liked_about_rp,
            'disliked_about_rp' => $this->disliked_about_rp,
            'other_remarks' => $this->other_remarks,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
