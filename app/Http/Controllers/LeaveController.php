<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\LeaveAssignment;
use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    // Leave Type Methods
    public function typeIndex()
    {
        $types = LeaveType::where('CDC_FLAG', 'A')->get();
        return response()->json($types);
    }

    public function typeStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'max_days' => 'nullable|integer',
            'is_paid' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $type = LeaveType::create([
            'name' => $request->name,
            'max_days' => $request->max_days,
            'is_paid' => $request->is_paid ?? true,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($type, 201);
    }

    // Leave Assignment Methods
    public function assignmentIndex(Request $request)
    {
        $query = LeaveAssignment::with(['user', 'leaveType'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $assignments = $query->get();
        return response()->json($assignments);
    }

    public function assignmentStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'leave_type_id' => 'required|exists:leave_types,leave_type_id',
            'allocated_days' => 'required|integer',
            'academic_year' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $assignment = LeaveAssignment::create([
            'user_id' => $request->user_id,
            'leave_type_id' => $request->leave_type_id,
            'allocated_days' => $request->allocated_days,
            'academic_year' => $request->academic_year,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($assignment, 201);
    }

    // Leave Application Methods
    public function applicationIndex(Request $request)
    {
        $query = LeaveApplication::with(['user', 'leaveType', 'approver'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->get();
        return response()->json($applications);
    }

    public function applicationStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'leave_type_id' => 'required|exists:leave_types,leave_type_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $application = LeaveApplication::create([
            'user_id' => $request->user_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'Pending',
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($application, 201);
    }

    public function applicationApprove(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'approver_id' => 'required|exists:users,user_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $application = LeaveApplication::where('application_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $application->update([
            'status' => 'Approved',
            'approver_id' => $request->approver_id
        ]);

        return response()->json($application);
    }

    public function applicationReject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'approver_id' => 'required|exists:users,user_id',
            'reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $application = LeaveApplication::where('application_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $application->update([
            'status' => 'Rejected',
            'approver_id' => $request->approver_id,
            'reason' => $request->reason
        ]);

        return response()->json($application);
    }
}