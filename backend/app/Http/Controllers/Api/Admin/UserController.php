<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('traineeProfile');

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)
                ->orWhere('username', 'like', $term)
                ->orWhere('email', 'like', $term));
        }

        $users = $query->orderBy('name')->paginate(min((int) $request->input('per_page', 15), 100) ?: 15);

        return UserResource::collection($users);
    }

    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        ActivityLog::record($request->user()->id, 'user.created', 'User', $user->id, "Created {$user->role} account for {$user->name}.");

        return new UserResource($user);
    }

    public function show(User $user)
    {
        return new UserResource($user->load('traineeProfile'));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        ActivityLog::record($request->user()->id, 'user.updated', 'User', $user->id, "Updated account for {$user->name}.");

        return new UserResource($user);
    }

    public function deactivate(Request $request, User $user)
    {
        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        ActivityLog::record($request->user()->id, 'user.deactivated', 'User', $user->id, "Deactivated account for {$user->name}.");

        return new UserResource($user);
    }

    public function activate(Request $request, User $user)
    {
        $user->update(['is_active' => true]);

        ActivityLog::record($request->user()->id, 'user.activated', 'User', $user->id, "Reactivated account for {$user->name}.");

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user)
    {
        $name = $user->name;
        $user->delete();

        ActivityLog::record($request->user()->id, 'user.deleted', 'User', $user->id, "Deleted account for {$name}.");

        return response()->json(['message' => 'User deleted.']);
    }
}
