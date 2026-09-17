<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingHistory extends Model
{
    use HasFactory;

    protected $table = 'training_history';

    protected $fillable = [
        'trainee_id',
        'training_program_id',
        'chain_level',
        'date_attended',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'date_attended' => 'date',
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }

    public function trainingProgram(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documentSubmissions(): HasMany
    {
        return $this->hasMany(DocumentSubmission::class);
    }
}
