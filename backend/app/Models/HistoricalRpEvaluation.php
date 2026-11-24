<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricalRpEvaluation extends Model
{
    protected $fillable = [
        'respondent_num',
        'respondent_name',
        'rp_code',
        'training_code',
        'resource_person_name',
        'training_title',
        'delivery_date',
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
        'source_file',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];
}
