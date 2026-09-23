<?php

use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompetencyGapController;
use App\Http\Controllers\Api\DocumentSubmissionController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RequiredDocumentController;
use App\Http\Controllers\Api\TraineeProfileController;
use App\Http\Controllers\Api\TraineeSelfController;
use App\Http\Controllers\Api\TraineeTestController;
use App\Http\Controllers\Api\TrainingConfirmationController;
use App\Http\Controllers\Api\TrainingHistoryController;
use App\Http\Controllers\Api\TrainingProgramController;
use App\Http\Controllers\Api\TrainingProgramTestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        Route::get('meta', [MetaController::class, 'index']);
        Route::get('meta/locations', [MetaController::class, 'locations']);

        // Administrator Module
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::apiResource('users', UserController::class)->except(['destroy']);
            Route::delete('users/{user}', [UserController::class, 'destroy']);
            Route::post('users/{user}/activate', [UserController::class, 'activate']);
            Route::post('users/{user}/deactivate', [UserController::class, 'deactivate']);
            Route::get('settings', [SettingController::class, 'index']);
            Route::put('settings', [SettingController::class, 'update']);
            Route::get('audit-logs', [AuditLogController::class, 'index']);
            Route::apiResource('required-documents', RequiredDocumentController::class)
                ->except(['show'])
                ->parameters(['required-documents' => 'requiredDocument']);
        });

        // Project Officer Module (admins may also perform these actions)
        Route::middleware('role:admin,project_officer')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'index']);
            Route::get('trainees', [TraineeProfileController::class, 'index']);
            Route::post('trainees', [TraineeProfileController::class, 'store']);
            Route::get('trainees/{trainee}', [TraineeProfileController::class, 'show']);
            Route::put('trainees/{trainee}', [TraineeProfileController::class, 'update']);
            Route::delete('trainees/{trainee}', [TraineeProfileController::class, 'destroy']);
            Route::post('trainees/{trainee}/link-account', [TraineeProfileController::class, 'linkAccount']);
            Route::get('trainees/{trainee}/activity-history', [TraineeProfileController::class, 'activityHistory']);
            Route::get('trainees/{trainee}/activity-history/print', [ReportController::class, 'traineeActivityHistoryPdf']);

            Route::get('trainees/{trainee}/training-history', [TrainingHistoryController::class, 'index']);
            Route::post('trainees/{trainee}/training-history', [TrainingHistoryController::class, 'store']);
            Route::put('training-history/{trainingHistory}', [TrainingHistoryController::class, 'update']);
            Route::delete('training-history/{trainingHistory}', [TrainingHistoryController::class, 'destroy']);

            Route::get('trainees/{trainee}/documents', [DocumentSubmissionController::class, 'forTrainee']);
            Route::get('training-history/{trainingHistory}/documents', [DocumentSubmissionController::class, 'forTrainingHistory']);
            Route::put('training-history/{trainingHistory}/documents/{requiredDocumentId}', [DocumentSubmissionController::class, 'update']);
            Route::get('training-history/{trainingHistory}/documents/{requiredDocumentId}/download', [DocumentSubmissionController::class, 'download']);

            Route::get('training-programs/{trainingProgram}/tests/{type}', [TrainingProgramTestController::class, 'show']);
            Route::put('training-programs/{trainingProgram}/tests/{type}', [TrainingProgramTestController::class, 'upsert']);
            Route::delete('training-programs/{trainingProgram}/tests/{type}', [TrainingProgramTestController::class, 'destroy']);

            Route::get('trainees/{trainee}/competency-gaps', [CompetencyGapController::class, 'forTrainee']);
            Route::get('competency-gaps', [CompetencyGapController::class, 'index']);

            Route::apiResource('training-programs', TrainingProgramController::class)
                ->parameters(['training-programs' => 'trainingProgram']);
            Route::get('training-programs-upcoming', [TrainingProgramController::class, 'upcoming']);
            Route::get('training-programs/{trainingProgram}/confirmations', [TrainingConfirmationController::class, 'forTrainingProgram']);
            Route::get('trainees/{trainee}/confirmations', [TrainingConfirmationController::class, 'forTrainee']);
            Route::post('confirmations/{confirmation}/respond', [TrainingConfirmationController::class, 'respond']);

            Route::get('reports/attendance', [ReportController::class, 'attendance']);
            Route::get('reports/document-compliance', [ReportController::class, 'documentCompliance']);
            Route::get('reports/competency-gaps', [ReportController::class, 'competencyGaps']);
            Route::get('reports/demographics', [ReportController::class, 'demographics']);
        });

        // Trainee Module: self-service only, scoped server-side to the caller's own profile
        Route::middleware('role:trainee')->prefix('trainee')->group(function () {
            Route::get('me', [TraineeSelfController::class, 'profile']);
            Route::get('me/documents', [TraineeSelfController::class, 'documentCompliance']);
            Route::get('me/competency-gaps', [TraineeSelfController::class, 'competencyGaps']);
            Route::get('me/confirmations', [TraineeSelfController::class, 'confirmations']);
            Route::post('me/confirmations/{confirmation}/respond', [TrainingConfirmationController::class, 'respond']);
            Route::get('me/activity-history', [TraineeSelfController::class, 'activityHistory']);

            Route::post('me/training-history/{trainingHistory}/documents/{requiredDocumentId}/upload', [DocumentSubmissionController::class, 'uploadAsTrainee']);
            Route::get('me/training-history/{trainingHistory}/documents/{requiredDocumentId}/download', [DocumentSubmissionController::class, 'download']);

            Route::get('me/training-history/{trainingHistory}/tests/{type}', [TraineeTestController::class, 'show']);
            Route::post('me/training-history/{trainingHistory}/tests/{type}/attempt', [TraineeTestController::class, 'attempt']);
        });
    });
});
