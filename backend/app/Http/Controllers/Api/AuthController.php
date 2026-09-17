<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
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
