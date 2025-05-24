<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClassesController extends Controller
{
    public function index(Request $request)
    {
        $query = Classes::with(['subject', 'teacher.user'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->has('section')) {
            $query->where('section', $request->section);
        }

        if ($request->has('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        $classes = $query->get();
        return response()->json($classes);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,subject_id',
            'teacher_id' => 'nullable|exists:teachers,teacher_id',
            'grade_level' => 'required|string|max:10',
            'section' => 'required|string|max:10',
            'academic_year' => 'required|string|max:10',
            'schedule' => 'required|json',
            'room_number' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $class = Classes::create([
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'grade_level' => $request->grade_level,
            'section' => $request->section,
            'academic_year' => $request->academic_year,
            'schedule' => $request->schedule,
            'room_number' => $request->room_number,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($class, 201);
    }

    public function show($id)
    {
        $class = Classes::with(['subject', 'teacher.user'])
            ->where('class_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($class);
    }

    public function update(Request $request, $id)
    {
        $class = Classes::where('class_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'subject_id' => 'sometimes|exists:subjects,subject_id',
            'teacher_id' => 'nullable|exists:teachers,teacher_id',
            'grade_level' => 'sometimes|string|max:10',
            'section' => 'sometimes|string|max:10',
            'academic_year' => 'sometimes|string|max:10',
            'schedule' => 'sometimes|json',
            'room_number' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create new version
        $newClass = $class->replicate();
        $newClass->fill($request->all());
        $newClass->save();

        // Invalidate old version
        $class->update([
            'CDC_FLAG' => 'I',
            'valid_to' => now()
        ]);

        return response()->json($newClass);
    }

    public function destroy($id)
    {
        $class = Classes::where('class_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $class->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }
}