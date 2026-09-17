<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'training_history_id',
        'required_document_id',
        'submitted',
        'date_submitted',
        'remarks',
        'updated_by',
    ];

    protected $casts = [
        'submitted' => 'boolean',
        'date_submitted' => 'date',
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }

    public function trainingHistory(): BelongsTo
    {
        return $this->belongsTo(TrainingHistory::class);
    }

    public function requiredDocument(): BelongsTo
    {
        return $this->belongsTo(RequiredDocument::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
