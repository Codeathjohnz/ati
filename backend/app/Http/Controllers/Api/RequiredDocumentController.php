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
        $name = $requiredDocument->name;
        $requiredDocument->delete();

        ActivityLog::record($request->user()->id, 'required_document.deleted', 'RequiredDocument', $requiredDocument->id, "Deleted required document \"{$name}\".");

        return response()->json(['message' => 'Required document deleted.']);
    }
}
