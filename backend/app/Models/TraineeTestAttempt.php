<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraineeTestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_program_test_id',
        'trainee_id',
        'training_history_id',
        'answers',
        'raw_score',
        'max_score',
        'percentage',
        'passed',
        'attempted_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'passed' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(TrainingProgramTest::class, 'training_program_test_id');
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }
}
