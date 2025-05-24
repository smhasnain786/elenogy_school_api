<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::where('CDC_FLAG', 'A')->get();
        return response()->json($schools);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|json',
            'phone' => 'nullable|string|max:20',
            'established_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $school = School::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'established_date' => $request->established_date,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($school, 201);
    }

    public function show($id)
    {
        $school = School::where('school_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($school);
    }

    public function update(Request $request, $id)
    {
        $school = School::where('school_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|json',
            'phone' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create new version with updated data
        $newSchool = $school->replicate();
        $newSchool->fill($request->all());
        $newSchool->save();

        // Invalidate old version
        $school->update([
            'CDC_FLAG' => 'I',
            'valid_to' => now()
        ]);

        return response()->json($newSchool);
    }

    public function destroy($id)
    {
        $school = School::where('school_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $school->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }
}