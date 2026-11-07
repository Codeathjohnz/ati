<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'certificate_number',
        'issued_at',
        'chain_levels',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'chain_levels' => 'array',
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(TraineeProfile::class, 'trainee_id');
    }
}
