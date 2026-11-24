<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpEvaluation extends Model
{
    use HasFactory;

    public const LIKERT_CRITERIA = [
        'clarity_objectives' => 'Clarity of the topic objectives at the beginning',
        'topic_organization' => 'Organization/sequencing of topic',
        'clarity_presentation' => 'Clarity of topic/ideas presented/discussed',
        'instructional_aids_quality' => 'Quality and effectiveness of instructional aids used',
        'teaching_ability' => 'Ability to teach/communicate ideas',
        'question_answering_ability' => 'Ability to answer questions',
        'participant_interest' => 'Ability to arouse/sustain interest',
        'time_management' => 'Ability to manage time',
        'topic_ending' => 'How the topic was ended',
        'overall_satisfaction' => 'Overall level of satisfaction',
    ];

    protected $fillable = [
        'trainee_id',
        'training_history_id',
        'topic_ratings',
        'clarity_objectives',
        'topic_organization',
        'clarity_presentation',
        'instructional_aids_quality',
        'teaching_ability',
        'question_answering_ability',
        'participant_interest',
        'time_management',
        'topic_ending',
        'overall_satisfaction',
        'liked_about_rp',
        'disliked_about_rp',
        'other_remarks',
    ];

    protected $casts = [
        'topic_ratings' => 'array',
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }

    public function trainingHistory(): BelongsTo
    {
        return $this->belongsTo(TrainingHistory::class);
    }
}
