<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    use HasFactory;

    public const CHAIN_LEVELS = [
        'Production', 'Post-Harvest', 'Processing', 'Marketing', 'Consumption',
    ];

    public const STATUSES = ['upcoming', 'ongoing', 'completed', 'cancelled'];

    protected $fillable = [
        'name',
        'description',
        'chain_level',
        'venue',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function trainingHistory(): HasMany
    {
        return $this->hasMany(TrainingHistory::class);
    }

    public function confirmations(): HasMany
    {
        return $this->hasMany(TrainingConfirmation::class);
    }

    public function tests(): HasMany
    {
        return $this->hasMany(TrainingProgramTest::class);
    }
}
