<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'user.school'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('grade_level')) {
            $query->where('grade_level', $request->grade_level);
        }

        if ($request->has('section')) {
            $query->where('section', $request->section);
        }

        $students = $query->get();
        return response()->json($students);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'enrollment_number' => 'required|string|unique:students',
            'grade_level' => 'required|string|max:10',
            'section' => 'required|string|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $student = Student::create([
            'student_id' => $request->user_id,
            'enrollment_number' => $request->enrollment_number,
            'grade_level' => $request->grade_level,
            'section' => $request->section,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($student, 201);
    }

    public function show($id)
    {
        $student = Student::with(['user', 'user.school'])
            ->where('student_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($student);
    }

    public function update(Request $request, $id)
    {
        $student = Student::where('student_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'enrollment_number' => 'sometimes|string|unique:students,enrollment_number,'.$id.',student_id',
            'grade_level' => 'sometimes|string|max:10',
            'section' => 'sometimes|string|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create new version
        $newStudent = $student->replicate();
        $newStudent->fill($request->all());
        $newStudent->save();

        // Invalidate old version
        $student->update([
            'CDC_FLAG' => 'I',
            'valid_to' => now()
        ]);

        return response()->json($newStudent);
    }

    public function destroy($id)
    {
        $student = Student::where('student_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $student->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }
}