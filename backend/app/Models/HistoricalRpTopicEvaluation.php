<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricalRpTopicEvaluation extends Model
{
    protected $fillable = [
        'respondent_num',
        'respondent_name',
        'rp_code',
        'training_code',
        'resource_person_name',
        'training_title',
        'delivery_date',
        'topic',
        'relevance',
        'time_allocation',
        'suggested_time_allocation',
        'methodology',
        'suggested_methodology',
        'source_file',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];
}
