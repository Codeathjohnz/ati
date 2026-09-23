<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgramTest extends Model
{
    use HasFactory;

    public const TYPE_PRE = 'pre_test';

    public const TYPE_POST = 'post_test';

    protected $fillable = [
        'training_program_id',
        'type',
        'title',
        'instructions',
        'passing_score',
        'created_by',
    ];

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TraineeTestAttempt::class);
    }
}
