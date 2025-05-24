<?php

namespace App\Http\Controllers;

use App\Models\SalaryStructure;
use App\Models\PayrollRecord;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    // Salary Structure Methods
    public function salaryIndex(Request $request)
    {
        $query = SalaryStructure::with(['user'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $salaries = $query->get();
        return response()->json($salaries);
    }

    public function salaryStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'base_salary' => 'required|numeric',
            'allowances' => 'nullable|json',
            'deductions' => 'nullable|json',
            'payment_cycle' => 'required|in:Monthly,Biweekly',
            'effective_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $salary = SalaryStructure::create([
            'user_id' => $request->user_id,
            'base_salary' => $request->base_salary,
            'allowances' => $request->allowances,
            'deductions' => $request->deductions,
            'payment_cycle' => $request->payment_cycle,
            'effective_date' => $request->effective_date,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($salary, 201);
    }

    // Payroll Record Methods
    public function payrollIndex(Request $request)
    {
        $query = PayrollRecord::with(['salary.user'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('salary_id')) {
            $query->where('salary_id', $request->salary_id);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $records = $query->get();
        return response()->json($records);
    }

    public function payrollStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salary_id' => 'required|exists:salary_structures,salary_id',
            'payment_date' => 'required|date',
            'gross_amount' => 'required|numeric',
            'net_amount' => 'required|numeric',
            'payment_method' => 'required|in:Bank Transfer,Cheque,Cash',
            'transaction_id' => 'nullable|string',
            'bank_account_details' => 'nullable|json',
            'tax_details' => 'nullable|json',
            'remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $record = PayrollRecord::create([
            'salary_id' => $request->salary_id,
            'payment_date' => $request->payment_date,
            'gross_amount' => $request->gross_amount,
            'net_amount' => $request->net_amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'bank_account_details' => $request->bank_account_details,
            'tax_details' => $request->tax_details,
            'payment_status' => 'Pending',
            'remarks' => $request->remarks,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($record, 201);
    }

    // Payslip Methods
    public function payslipIndex(Request $request)
    {
        $query = Payslip::with(['payroll.salary.user', 'document'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('payroll_id')) {
            $query->where('payroll_id', $request->payroll_id);
        }

        if ($request->has('payslip_period')) {
            $query->where('payslip_period', $request->payslip_period);
        }

        $payslips = $query->get();
        return response()->json($payslips);
    }

    public function payslipStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payroll_id' => 'required|exists:payroll_records,payroll_id',
            'document_id' => 'required|exists:documents,doc_id',
            'payslip_period' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $payslip = Payslip::create([
            'payroll_id' => $request->payroll_id,
            'document_id' => $request->document_id,
            'payslip_period' => $request->payslip_period,
            'generation_date' => now(),
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($payslip, 201);
    }
}