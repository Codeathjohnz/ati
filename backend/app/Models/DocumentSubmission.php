<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSubmission extends Model
{
    use HasFactory;

    public const REVIEW_PENDING = 'pending';

    public const REVIEW_APPROVED = 'approved';

    public const REVIEW_NEEDS_REVISION = 'needs_revision';

    protected $fillable = [
        'trainee_id',
        'training_history_id',
        'required_document_id',
        'submitted',
        'date_submitted',
        'remarks',
        'updated_by',
        'attachment_path',
        'attachment_original_name',
        'review_status',
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
