<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'user.school'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('department')) {
            $query->where('department', $request->department);
        }

        $teachers = $query->get();
        return response()->json($teachers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'employee_code' => 'required|string|unique:teachers',
            'department' => 'nullable|string|max:50',
            'subject_specialization' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $teacher = Teacher::create([
            'teacher_id' => $request->user_id,
            'employee_code' => $request->employee_code,
            'department' => $request->department,
            'subject_specialization' => $request->subject_specialization,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($teacher, 201);
    }

    public function show($id)
    {
        $teacher = Teacher::with(['user', 'user.school'])
            ->where('teacher_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($teacher);
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::where('teacher_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'employee_code' => 'sometimes|string|unique:teachers,employee_code,'.$id.',teacher_id',
            'department' => 'nullable|string|max:50',
            'subject_specialization' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create new version
        $newTeacher = $teacher->replicate();
        $newTeacher->fill($request->all());
        $newTeacher->save();

        // Invalidate old version
        $teacher->update([
            'CDC_FLAG' => 'I',
            'valid_to' => now()
        ]);

        return response()->json($newTeacher);
    }

    public function destroy($id)
    {
        $teacher = Teacher::where('teacher_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $teacher->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }
}