<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TraineeProfileStoreRequest;
use App\Http\Requests\TraineeProfileUpdateRequest;
use App\Http\Resources\TraineeProfileResource;
use App\Http\Resources\TrainingConfirmationResource;
use App\Http\Resources\TrainingHistoryResource;
use App\Models\ActivityLog;
use App\Models\TraineeProfile;
use App\Models\User;
use App\Services\CompetencyGapAnalysisService;
use App\Services\DocumentComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TraineeProfileController extends Controller
{
    /**
     * Advanced search & filtering (REQ0012 / Objective 1): sex, age, province,
     * city/municipality, LGU income class, year last attended, organization
     * or affiliation, and educational level, plus a free-text name/contact search.
     */
    public function index(Request $request)
    {
        $query = TraineeProfile::query()->withCount('trainingHistory as training_count');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('middle_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('contact_number', 'like', $term);
            });
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->string('sex'));
        }

        if ($request->filled('province')) {
            $query->where('province', $request->string('province'));
        }

        if ($request->filled('city_municipality')) {
            $query->where('city_municipality', $request->string('city_municipality'));
        }

        if ($request->filled('lgu_income_class')) {
            $query->where('lgu_income_class', $request->string('lgu_income_class'));
        }

        if ($request->filled('educational_level')) {
            $query->where('educational_level', $request->string('educational_level'));
        }

        if ($request->filled('organization_affiliation')) {
            $query->where('organization_affiliation', 'like', '%'.$request->string('organization_affiliation').'%');
        }

        if ($request->filled('age_min')) {
            $query->whereDate('birthdate', '<=', now()->subYears((int) $request->input('age_min'))->toDateString());
        }

        if ($request->filled('age_max')) {
            $query->whereDate('birthdate', '>=', now()->subYears((int) $request->input('age_max') + 1)->addDay()->toDateString());
        }

        if ($request->filled('year_last_attended')) {
            $year = (int) $request->input('year_last_attended');
            $query->whereHas('trainingHistory', function ($q) use ($year) {
                $q->whereYear('date_attended', $year);
            });
        }

        $query->orderBy('last_name')->orderBy('first_name');

        $perPage = min((int) $request->input('per_page', 15), 100) ?: 15;
        $trainees = $query->paginate($perPage);

        return TraineeProfileResource::collection($trainees);
    }

    public function store(TraineeProfileStoreRequest $request)
    {
        $data = $request->validated();

        $userId = null;
        if ($request->boolean('create_login')) {
            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'username' => $data['username'],
                'email' => $data['email'] ?? $data['username'].'@ati-caraga.local',
                'role' => User::ROLE_TRAINEE,
                'password' => Hash::make($data['password']),
                'is_active' => true,
            ]);
            $userId = $user->id;
        }

        $trainee = TraineeProfile::create([
            ...collect($data)->except(['create_login', 'username', 'password'])->all(),
            'user_id' => $userId,
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::record($request->user()->id, 'trainee.created', 'TraineeProfile', $trainee->id, "Registered trainee {$trainee->full_name}.");

        return new TraineeProfileResource($trainee);
    }

    public function show(Request $request, TraineeProfile $trainee)
    {
        $trainee->load(['trainingHistory.trainingProgram']);

        return new TraineeProfileResource($trainee);
    }

    public function update(TraineeProfileUpdateRequest $request, TraineeProfile $trainee)
    {
        $trainee->update($request->validated());

        ActivityLog::record($request->user()->id, 'trainee.updated', 'TraineeProfile', $trainee->id, "Updated profile of {$trainee->full_name}.");

        return new TraineeProfileResource($trainee);
    }

    public function destroy(Request $request, TraineeProfile $trainee)
    {
        $name = $trainee->full_name;
        DB::transaction(function () use ($trainee) {
            $trainee->documentSubmissions()->delete();
            $trainee->confirmations()->delete();
            $trainee->trainingHistory()->delete();
            $trainee->delete();
        });

        ActivityLog::record($request->user()->id, 'trainee.deleted', 'TraineeProfile', $trainee->id, "Deleted trainee {$name}.");

        return response()->json(['message' => 'Trainee deleted.']);
    }

    /**
     * Activity History Search (REQ0014 / REQ0020): a trainee's complete,
     * printable record of training activities and document submissions.
     */
    public function activityHistory(
        Request $request,
        TraineeProfile $trainee,
        DocumentComplianceService $documents,
        CompetencyGapAnalysisService $gaps
    ) {
        $trainee->load(['trainingHistory.trainingProgram', 'confirmations.trainingProgram']);

        $logs = ActivityLog::query()
            ->where('subject_type', 'TraineeProfile')
            ->where('subject_id', $trainee->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return response()->json([
            'trainee' => new TraineeProfileResource($trainee),
            'training_history' => TrainingHistoryResource::collection($trainee->trainingHistory),
            'document_compliance' => $documents->evaluateForTrainee($trainee),
            'competency_gaps' => $gaps->summarize($trainee),
            'confirmations' => TrainingConfirmationResource::collection($trainee->confirmations),
            'activity_log' => $logs,
        ]);
    }

    /**
     * Create and link a portal login account for a trainee who was
     * registered without one ("set it up later" from the registration form).
     */
    public function linkAccount(Request $request, TraineeProfile $trainee)
    {
        if ($trainee->user_id) {
            return response()->json(['message' => 'This trainee already has a linked login account.'], 422);
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:150'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $trainee->full_name,
            'username' => $validated['username'],
            'email' => $validated['email'] ?? $trainee->email ?? $validated['username'].'@ati-caraga.local',
            'role' => User::ROLE_TRAINEE,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $trainee->update(['user_id' => $user->id]);

        ActivityLog::record($request->user()->id, 'trainee.account_linked', 'TraineeProfile', $trainee->id, "Linked a login account for {$trainee->full_name}.");

        return new TraineeProfileResource($trainee->fresh());
    }

    /**
     * Resolve the authenticated trainee's own profile (Trainee Module).
     */
    public function me(Request $request)
    {
        $trainee = $request->user()->traineeProfile;

        if (! $trainee) {
            return response()->json(['message' => 'No trainee profile linked to this account.'], 404);
        }

        $trainee->load(['trainingHistory.trainingProgram']);

        return new TraineeProfileResource($trainee);
    }
}
