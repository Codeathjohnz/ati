<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrainingHistoryResource;
use App\Models\ActivityLog;
use App\Models\TrainingHistory;
use App\Models\TrainingProgram;
use App\Services\AttendanceService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AttendanceController extends Controller
{
    /**
     * Officer/Admin: a QR code a trainee can scan on training day to record
     * their own attendance. The payload (program id + expiry) is encrypted
     * with the app key — tamper-proof and self-contained, so verifying it
     * later needs no separate token store. Scanning it with any camera app
     * just opens the frontend's /attend page; no in-app scanner needed.
     */
    public function generateQr(Request $request, TrainingProgram $trainingProgram)
    {
        $hours = min((int) $request->input('hours', 24), 24 * 14);

        $token = Crypt::encryptString(json_encode([
            'training_program_id' => $trainingProgram->id,
            'expires_at' => now()->addHours($hours)->timestamp,
        ]));

        $attendUrl = rtrim(config('app.frontend_url'), '/').'/attend?token='.urlencode($token);

        $qr = (new Builder(writer: new PngWriter(), data: $attendUrl, size: 280, margin: 8))->build();

        return response()->json([
            'qr_data_uri' => $qr->getDataUri(),
            'attend_url' => $attendUrl,
            'expires_at' => now()->addHours($hours)->toIso8601String(),
        ]);
    }

    /** Trainee: redeems a scanned QR code, recording their own attendance. */
    public function scan(Request $request, AttendanceService $attendance)
    {
        $request->validate(['token' => ['required', 'string']]);

        try {
            $payload = json_decode(Crypt::decryptString($request->input('token')), true);
        } catch (\Exception) {
            abort(422, 'This attendance code is invalid.');
        }

        if (! is_array($payload) || ($payload['expires_at'] ?? 0) < now()->timestamp) {
            abort(422, 'This attendance code has expired. Ask your Project Officer for a new one.');
        }

        $program = TrainingProgram::find($payload['training_program_id'] ?? null);
        abort_unless($program, 404, 'That training program no longer exists.');

        $trainee = $request->user()->traineeProfile;
        abort_unless($trainee, 404, 'No trainee profile linked to this account.');

        $existing = TrainingHistory::where('trainee_id', $trainee->id)
            ->where('training_program_id', $program->id)
            ->first();

        if ($existing) {
            return new TrainingHistoryResource($existing->load('trainingProgram'));
        }

        $history = $attendance->record($trainee, $program, now()->toDateString(), 'Recorded via QR code attendance.', $request->user()->id);

        ActivityLog::record(
            $request->user()->id,
            'training_history.created',
            'TraineeProfile',
            $trainee->id,
            "{$trainee->full_name} checked in via QR code for \"{$program->name}\"."
        );

        return new TrainingHistoryResource($history->load('trainingProgram'));
    }
}
