<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_PROJECT_OFFICER = 'project_officer';
    public const ROLE_TRAINEE = 'trainee';

    public const ROLES = [self::ROLE_ADMIN, self::ROLE_PROJECT_OFFICER, self::ROLE_TRAINEE];

    protected $fillable = [
        'name',
        'username',
        'email',
        'role',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function traineeProfile(): HasOne
    {
        return $this->hasOne(TraineeProfile::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isProjectOfficer(): bool
    {
        return $this->role === self::ROLE_PROJECT_OFFICER;
    }

    public function isTrainee(): bool
    {
        return $this->role === self::ROLE_TRAINEE;
    }
}
