<?php

namespace App\Http\Controllers;

use App\Models\FeeTypes;
use App\Models\FeeStructure;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FinanceController extends Controller
{
    // Fee Type Methods
    public function feeTypeIndex()
    {
        $feeTypes = FeeTypes::where('CDC_FLAG', 'A')->get();
        return response()->json($feeTypes);
    }

    public function feeTypeStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_name' => 'required|string|unique:fee_types',
            'description' => 'nullable|string',
            'is_mandatory' => 'boolean',
            'frequency' => 'required|in:One-Time,Monthly,Termly,Annual'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $feeType = FeeTypes::create([
            'fee_name' => $request->fee_name,
            'description' => $request->description,
            'is_mandatory' => $request->is_mandatory ?? true,
            'frequency' => $request->frequency,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($feeType, 201);
    }

    // Fee Structure Methods
    public function feeStructureIndex(Request $request)
    {
        $query = FeeStructure::with(['feeType', 'school'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->has('academic_term')) {
            $query->where('academic_term', $request->academic_term);
        }

        $structures = $query->get();
        return response()->json($structures);
    }

    public function feeStructureStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fee_type_id' => 'required|exists:fee_types,fee_type_id',
            'school_id' => 'required|exists:schools,school_id',
            'amount' => 'required|numeric',
            'academic_term' => 'nullable|string',
            'due_date' => 'required|date',
            'late_fine_per_day' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $structure = FeeStructure::create([
            'fee_type_id' => $request->fee_type_id,
            'school_id' => $request->school_id,
            'amount' => $request->amount,
            'academic_term' => $request->academic_term,
            'due_date' => $request->due_date,
            'late_fine_per_day' => $request->late_fine_per_day,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($structure, 201);
    }

    // Student Fee Assignment Methods
    public function assignmentIndex(Request $request)
    {
        $query = StudentFeeAssignment::with(['student.user', 'structure.feeType'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $assignments = $query->get();
        return response()->json($assignments);
    }

    public function assignmentStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,student_id',
            'structure_id' => 'required|exists:fee_structures,structure_id',
            'waived_amount' => 'nullable|numeric',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $assignment = StudentFeeAssignment::create([
            'student_id' => $request->student_id,
            'structure_id' => $request->structure_id,
            'waived_amount' => $request->waived_amount ?? 0,
            'notes' => $request->notes,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($assignment, 201);
    }

    // Payment Methods
    public function paymentIndex(Request $request)
    {
        $query = FeePayment::with(['assignment.student.user', 'verifiedBy.user'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('assignment_id')) {
            $query->where('assignment_id', $request->assignment_id);
        }

        if ($request->has('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        $payments = $query->get();
        return response()->json($payments);
    }

    public function paymentStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'required|exists:student_fee_assignments,assignment_id',
            'amount_paid' => 'required|numeric',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Cheque,Online,Bank Transfer',
            'transaction_id' => 'nullable|string',
            'payment_proof_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $payment = FeePayment::create([
            'assignment_id' => $request->assignment_id,
            'amount_paid' => $request->amount_paid,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'payment_proof_url' => $request->payment_proof_url,
            'verification_status' => 'Pending',
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($payment, 201);
    }

    public function paymentVerify(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'verified_by' => 'required|exists:staff,staff_id',
            'verification_status' => 'required|in:Verified,Rejected',
            'verification_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $payment = FeePayment::where('payment_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $payment->update([
            'verification_status' => $request->verification_status,
            'verified_by' => $request->verified_by,
            'verification_notes' => $request->verification_notes
        ]);

        return response()->json($payment);
    }
}