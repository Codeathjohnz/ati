<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Bootstraps exactly one administrator account so the system is reachable on
 * first run. All Project Officer and Trainee accounts are meant to be
 * created afterwards, for real, through the Admin > Users module.
 *
 * The username/email/password default to the values used throughout local
 * testing, but are overridable via ADMIN_USERNAME / ADMIN_EMAIL /
 * ADMIN_PASSWORD env vars — set those before deploying to a live server so
 * the well-known testing password is never the one exposed publicly.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $existing = User::where('username', $username)->first();

        $attributes = [
            'name' => 'System Administrator',
            'email' => env('ADMIN_EMAIL', 'admin@ati-caraga.local'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ];

        // Password is set only at creation time. This seeder re-runs on
        // every deploy (it's how reference data like required documents and
        // locations stays in sync), so never re-hash it here afterwards —
        // that would silently overwrite a password the admin has since
        // changed through Admin > Users with the env default again.
        if (! $existing) {
            $attributes['password'] = Hash::make(env('ADMIN_PASSWORD', 'Admin@12345'));
        }

        User::updateOrCreate(['username' => $username], $attributes);
    }
}
