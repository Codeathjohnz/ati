<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    private const TITLES = [
        User::ROLE_ADMIN => 'Administrator User Manual',
        User::ROLE_PROJECT_OFFICER => 'Project Officer User Manual',
        User::ROLE_TRAINEE => 'Trainee User Manual',
    ];

    public function manual(Request $request)
    {
        $view = $this->viewFor($request);

        return response()->json([
            'title' => self::TITLES[$request->user()->role],
            'html' => view($view)->render(),
        ]);
    }

    public function manualPdf(Request $request)
    {
        $view = $this->viewFor($request);

        $pdf = Pdf::loadView($view)->setPaper('a4', 'portrait');

        return $pdf->stream(str_replace(' ', '-', strtolower(self::TITLES[$request->user()->role])).'.pdf');
    }

    private function viewFor(Request $request): string
    {
        return match ($request->user()->role) {
            User::ROLE_ADMIN => 'manual.admin',
            User::ROLE_PROJECT_OFFICER => 'manual.officer',
            default => 'manual.trainee',
        };
    }
}
