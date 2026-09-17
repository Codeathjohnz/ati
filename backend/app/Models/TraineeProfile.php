<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TraineeProfile extends Model
{
    use HasFactory;

    public const EDUCATIONAL_LEVELS = [
        'Elementary', 'High School', 'Vocational', 'College', 'Post Graduate',
    ];

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'birthdate',
        'contact_number',
        'email',
        'address_line',
        'barangay',
        'city_municipality',
        'province',
        'region',
        'lgu_income_class',
        'educational_level',
        'organization_affiliation',
        'occupation',
        'created_by',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    protected $appends = ['full_name', 'age'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function trainingHistory(): HasMany
    {
        return $this->hasMany(TrainingHistory::class, 'trainee_id');
    }

    public function documentSubmissions(): HasMany
    {
        return $this->hasMany(DocumentSubmission::class, 'trainee_id');
    }

    public function confirmations(): HasMany
    {
        return $this->hasMany(TrainingConfirmation::class, 'trainee_id');
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name, $this->suffix]);

        return implode(' ', $parts);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthdate ? Carbon::parse($this->birthdate)->age : null;
    }
}
