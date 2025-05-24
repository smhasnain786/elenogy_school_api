<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::where('CDC_FLAG', 'A');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('doc_type')) {
            $query->where('doc_type', $request->doc_type);
        }

        $documents = $query->get();
        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'doc_type' => 'required|string|max:50',
            'category' => 'required|string|max:20',
            'file' => 'required|file'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $file = $request->file('file');
        $path = $file->store('documents');

        $document = Document::create([
            'user_id' => $request->user_id,
            'doc_type' => $request->doc_type,
            'category' => $request->category,
            'file_path' => $path,
            'version' => 1,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($document, 201);
    }

    public function show($id)
    {
        $document = Document::where('doc_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($document);
    }

    public function download($id)
    {
        $document = Document::where('doc_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        return Storage::download($document->file_path);
    }

    public function destroy($id)
    {
        $document = Document::where('doc_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        Storage::delete($document->file_path);

        $document->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }
}