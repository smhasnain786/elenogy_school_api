<?php

namespace App\Http\Controllers;

use App\Models\TransportVehicle;
use App\Models\TransportAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransportController extends Controller
{
    // Vehicle Methods
    public function vehicleIndex()
    {
        $vehicles = TransportVehicle::with(['driver.user'])
            ->where('CDC_FLAG', 'A')
            ->get();
        return response()->json($vehicles);
    }

    public function vehicleStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_number' => 'required|string|unique:transport_vehicles',
            'driver_id' => 'required|exists:staff,staff_id',
            'capacity' => 'required|integer',
            'route_details' => 'required|json'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $vehicle = TransportVehicle::create([
            'vehicle_number' => $request->vehicle_number,
            'driver_id' => $request->driver_id,
            'capacity' => $request->capacity,
            'route_details' => $request->route_details,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($vehicle, 201);
    }

    // Attendance Methods
    public function attendanceIndex(Request $request)
    {
        $query = TransportAttendance::with(['student.user', 'vehicle'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('date')) {
            $query->whereDate('boarding_time', $request->date);
        }

        $attendance = $query->get();
        return response()->json($attendance);
    }

    public function attendanceStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id',
            'vehicle_id' => 'required|exists:transport_vehicles,vehicle_id',
            'boarding_time' => 'required|date',
            'boarding_latitude' => 'required|numeric',
            'boarding_longitude' => 'required|numeric',
            'dropoff_time' => 'nullable|date',
            'dropoff_latitude' => 'nullable|numeric',
            'dropoff_longitude' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $attendance = TransportAttendance::create([
            'student_id' => $request->student_id,
            'vehicle_id' => $request->vehicle_id,
            'boarding_time' => $request->boarding_time,
            'boarding_latitude' => $request->boarding_latitude,
            'boarding_longitude' => $request->boarding_longitude,
            'dropoff_time' => $request->dropoff_time,
            'dropoff_latitude' => $request->dropoff_latitude,
            'dropoff_longitude' => $request->dropoff_longitude,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($attendance, 201);
    }
}