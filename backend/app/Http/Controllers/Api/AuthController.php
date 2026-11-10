<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private const GOOGLE_CERTS_URL = 'https://www.googleapis.com/oauth2/v3/certs';
    private const GOOGLE_ISSUERS = ['accounts.google.com', 'https://accounts.google.com'];

    /**
     * "Sign in with Google" for trainees only, and only for an account that
     * already exists here (a Project Officer must have created the trainee
     * profile and granted portal access first) — this is a faster way to log
     * into an existing account, not a self-registration path.
     */
    public function google(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $clientId = config('services.google.client_id');
        if (! $clientId) {
            return response()->json(['message' => 'Google sign-in is not configured on this server.'], 500);
        }

        try {
            $jwks = Cache::remember('google.jwks', 3600, fn () => Http::get(self::GOOGLE_CERTS_URL)->throw()->json());
            $payload = JWT::decode($request->string('credential'), JWK::parseKeySet($jwks));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Could not verify this Google sign-in. Please try again.'], 401);
        }

        if (
            ! in_array($payload->iss ?? null, self::GOOGLE_ISSUERS, true)
            || ($payload->aud ?? null) !== $clientId
            || empty($payload->email_verified)
        ) {
            return response()->json(['message' => 'Could not verify this Google sign-in. Please try again.'], 401);
        }

        $user = User::query()
            ->where('email', $payload->email)
            ->where('role', User::ROLE_TRAINEE)
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'No trainee account matches this Google email. Ask your Project Officer to set up your portal access first.',
            ], 404);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Your account has been deactivated. Contact the administrator.'], 403);
        }

        $token = $user->createToken('ati-caraga-session')->plainTextToken;

        ActivityLog::record($user->id, 'login', 'User', $user->id, "{$user->name} logged in with Google.");

        return response()->json([
            'token' => $token,
            'user' => $user->load('traineeProfile'),
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $user = User::query()
            ->where('username', $request->string('username'))
            ->orWhere('email', $request->string('username'))
            ->first();

        // Checked directly against the hash rather than via Auth::getProvider(), which
        // resolves against whatever guard happens to be "current" for this request and
        // is not reliable in every context (e.g. changes after actingAs() in tests).
        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Your account has been deactivated. Contact the administrator.'], 403);
        }

        $token = $user->createToken('ati-caraga-session')->plainTextToken;

        ActivityLog::record($user->id, 'login', 'User', $user->id, "{$user->name} logged in.");

        return response()->json([
            'token' => $token,
            'user' => $user->load('traineeProfile'),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user?->currentAccessToken()?->delete();

        if ($user) {
            ActivityLog::record($user->id, 'logout', 'User', $user->id, "{$user->name} logged out.");
        }

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('traineeProfile'),
        ]);
    }
}
