<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\RecurringHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    // Holiday Methods
    public function index(Request $request)
    {
        $query = Holiday::with(['school'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $holidays = $query->get();
        return response()->json($holidays);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'school_id' => 'required|exists:schools,school_id',
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'academic_year' => 'required|string',
            'type' => 'required|in:National,Religious,Academic',
            'is_recurring' => 'boolean',
            'is_public' => 'boolean',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $holiday = Holiday::create([
            'school_id' => $request->school_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'academic_year' => $request->academic_year,
            'type' => $request->type,
            'is_recurring' => $request->is_recurring ?? false,
            'is_public' => $request->is_public ?? true,
            'description' => $request->description,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($holiday, 201);
    }

    // Recurring Holiday Methods
    public function recurringIndex(Request $request)
    {
        $query = RecurringHoliday::with(['holiday'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('holiday_id')) {
            $query->where('holiday_id', $request->holiday_id);
        }

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        $recurring = $query->get();
        return response()->json($recurring);
    }

    public function recurringStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'holiday_id' => 'required|exists:holidays,holiday_id',
            'year' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $recurring = RecurringHoliday::create([
            'holiday_id' => $request->holiday_id,
            'year' => $request->year,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($recurring, 201);
    }
}