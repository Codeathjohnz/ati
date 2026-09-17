<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(
            Setting::query()->pluck('value', 'key')
        );
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::set($key, $value);
        }

        ActivityLog::record($request->user()->id, 'settings.updated', 'Setting', null, 'Updated system settings.', $validated['settings']);

        return response()->json(Setting::query()->pluck('value', 'key'));
    }
}
