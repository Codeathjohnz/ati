<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // This is an API-only backend (no Blade "login" route exists), so
        // unauthenticated requests always fall through to a 401 JSON response.
        return null;
    }
}
