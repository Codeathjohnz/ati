<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RequiredDocument;
use Illuminate\Http\Request;

class RequiredDocumentController extends Controller
{
    public function index()
    {
        return response()->json(
            RequiredDocument::query()->orderBy('sort_order')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:required_documents,code'],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $document = RequiredDocument::create($validated);

        ActivityLog::record($request->user()->id, 'required_document.created', 'RequiredDocument', $document->id, "Added required document \"{$document->name}\".");

        return response()->json($document, 201);
    }

    public function update(Request $request, RequiredDocument $requiredDocument)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:required_documents,code,'.$requiredDocument->id],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $requiredDocument->update($validated);

        ActivityLog::record($request->user()->id, 'required_document.updated', 'RequiredDocument', $requiredDocument->id, "Updated required document \"{$requiredDocument->name}\".");

        return response()->json($requiredDocument);
    }

    public function destroy(Request $request, RequiredDocument $requiredDocument)
    {
        // Deleting cascades to every document_submissions row for this
        // document (see its migration), which would silently erase real
        // Yes/No compliance history across every trainee and training it
        // was ever tracked on. Once any submission exists, "removing" a
        // document must go through Deactivate instead — that stops it
        // appearing on future trainings without touching past records. A
        // hard delete is only for a document that was never actually used
        // (e.g. added by mistake).
        if ($requiredDocument->documentSubmissions()->exists()) {
            return response()->json([
                'message' => "\"{$requiredDocument->name}\" already has document records against it and can't be deleted — deactivate it instead to stop it applying to future trainings without losing past compliance history.",
            ], 422);
        }

        $name = $requiredDocument->name;
        $requiredDocument->delete();

        ActivityLog::record($request->user()->id, 'required_document.deleted', 'RequiredDocument', $requiredDocument->id, "Deleted required document \"{$name}\".");

        return response()->json(['message' => 'Required document deleted.']);
    }
}
