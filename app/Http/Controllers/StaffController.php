<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with(['user', 'user.school'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('position')) {
            $query->where('position', $request->position);
        }

        $staff = $query->get();
        return response()->json($staff);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'employee_code' => 'required|string|unique:staff',
            'position' => 'required|string|max:50',
            'office_number' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $staff = Staff::create([
            'staff_id' => $request->user_id,
            'employee_code' => $request->employee_code,
            'position' => $request->position,
            'office_number' => $request->office_number,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($staff, 201);
    }

    public function show($id)
    {
        $staff = Staff::with(['user', 'user.school'])
            ->where('staff_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($staff);
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::where('staff_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'employee_code' => 'sometimes|string|unique:staff,employee_code,'.$id.',staff_id',
            'position' => 'sometimes|string|max:50',
            'office_number' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create new version
        $newStaff = $staff->replicate();
        $newStaff->fill($request->all());
        $newStaff->save();

        // Invalidate old version
        $staff->update([
            'CDC_FLAG' => 'I',
            'valid_to' => now()
        ]);

        return response()->json($newStaff);
    }

    public function destroy($id)
    {
        $staff = Staff::where('staff_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $staff->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }
}