<?php

namespace App\Http\Controllers;

use App\Models\ClassAttendance;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    // Student Attendance Methods
    public function studentIndex(Request $request)
    {
        $query = ClassAttendance::with(['student.user', 'class'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        $attendance = $query->get();
        return response()->json($attendance);
    }

    public function studentStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id',
            'class_id' => 'required|exists:classes,class_id',
            'date' => 'required|date',
            'status' => 'required|in:Present,Late,Absent'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $attendance = ClassAttendance::create([
            'student_id' => $request->student_id,
            'class_id' => $request->class_id,
            'date' => $request->date,
            'status' => $request->status,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($attendance, 201);
    }

    // Staff Attendance Methods
    public function staffIndex(Request $request)
    {
        $query = StaffAttendance::with(['staff.user', 'teacher.user', 'class'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        $attendance = $query->get();
        return response()->json($attendance);
    }

    public function staffStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'nullable|exists:staff,staff_id',
            'teacher_id' => 'nullable|exists:teachers,teacher_id',
            'class_id' => 'nullable|exists:classes,class_id',
            'date' => 'required|date',
            'status' => 'required|in:Present,Late,Absent,On Leave'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$request->staff_id && !$request->teacher_id) {
            return response()->json(['error' => 'Either staff_id or teacher_id is required'], 422);
        }

        $attendance = StaffAttendance::create([
            'staff_id' => $request->staff_id,
            'teacher_id' => $request->teacher_id,
            'class_id' => $request->class_id,
            'date' => $request->date,
            'status' => $request->status,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($attendance, 201);
    }
}