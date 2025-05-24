<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::where('CDC_FLAG', 'A');

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        $subjects = $query->get();
        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_code' => 'required|string|unique:subjects',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'department' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $subject = Subject::create([
            'subject_code' => $request->subject_code,
            'name' => $request->name,
            'description' => $request->description,
            'department' => $request->department,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($subject, 201);
    }

    public function show($id)
    {
        $subject = Subject::where('subject_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($subject);
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::where('subject_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'subject_code' => 'sometimes|string|unique:subjects,subject_code,'.$id.',subject_id',
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'department' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create new version
        $newSubject = $subject->replicate();
        $newSubject->fill($request->all());
        $newSubject->save();

        // Invalidate old version
        $subject->update([
            'CDC_FLAG' => 'I',
            'valid_to' => now()
        ]);

        return response()->json($newSubject);
    }

    public function destroy($id)
    {
        $subject = Subject::where('subject_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $subject->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }
}