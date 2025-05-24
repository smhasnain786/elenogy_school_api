<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserRoleController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['jwt.csrf'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.csrf'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | School Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('schools', SchoolController::class);

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('users', UserController::class);

    // User Roles
    Route::prefix('users/{user}')->group(function () {
        Route::get('roles', [UserRoleController::class, 'index']);
        Route::post('roles', [UserRoleController::class, 'store']);
        Route::delete('roles/{role}', [UserRoleController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Student Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('students', StudentController::class);

    /*
    |--------------------------------------------------------------------------
    | Teacher Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('teachers', TeacherController::class);

    /*
    |--------------------------------------------------------------------------
    | Staff Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('staff', StaffController::class);

    /*
    |--------------------------------------------------------------------------
    | Academic Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('subjects', SubjectController::class);
    Route::apiResource('classes', ClassesController::class);

    // Class Students
    Route::prefix('classes/{class}')->group(function () {
        Route::get('students', [ClassesController::class, 'students']);
        Route::post('students', [ClassesController::class, 'addStudent']);
        Route::delete('students/{student}', [ClassesController::class, 'removeStudent']);
    });

    /*
    |--------------------------------------------------------------------------
    | Attendance Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('attendance')->group(function () {
        // Student Attendance
        Route::get('students', [AttendanceController::class, 'studentIndex']);
        Route::post('students', [AttendanceController::class, 'studentStore']);

        // Staff Attendance
        Route::get('staff', [AttendanceController::class, 'staffIndex']);
        Route::post('staff', [AttendanceController::class, 'staffStore']);
    });

    /*
    |--------------------------------------------------------------------------
    | Transport Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('transport')->group(function () {
        // Vehicles
        Route::apiResource('vehicles', TransportController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

        // Attendance
        Route::get('attendance', [TransportController::class, 'attendanceIndex']);
        Route::post('attendance', [TransportController::class, 'attendanceStore']);
    });

    /*
    |--------------------------------------------------------------------------
    | Financial Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('finance')->group(function () {
        // Fee Types
        Route::apiResource('fee-types', FinanceController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

        // Fee Structures
        Route::apiResource('fee-structures', FinanceController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

        // Student Fee Assignments
        Route::apiResource('fee-assignments', FinanceController::class)->only(['index', 'store', 'show']);

        // Payments
        Route::apiResource('payments', FinanceController::class)->only(['index', 'store', 'show']);
        Route::post('payments/{payment}/verify', [FinanceController::class, 'paymentVerify']);
    });

    /*
    |--------------------------------------------------------------------------
    | Payroll Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('payroll')->group(function () {
        // Salary Structures
        Route::apiResource('salaries', PayrollController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

        // Payroll Records
        Route::apiResource('records', PayrollController::class)->only(['index', 'store', 'show']);

        // Payslips
        Route::apiResource('payslips', PayrollController::class)->only(['index', 'store', 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Leave Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('leave')->group(function () {
        // Leave Types
        Route::apiResource('types', LeaveController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

        // Leave Assignments
        Route::apiResource('assignments', LeaveController::class)->only(['index', 'store', 'show']);

        // Applications
        Route::apiResource('applications', LeaveController::class)->only(['index', 'store', 'show']);
        Route::post('applications/{application}/approve', [LeaveController::class, 'applicationApprove']);
        Route::post('applications/{application}/reject', [LeaveController::class, 'applicationReject']);
    });

    /*
    |--------------------------------------------------------------------------
    | Holiday Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('holidays')->group(function () {
        // Holidays
        Route::apiResource('/', HolidayController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

        // Recurring Holidays
        Route::apiResource('recurring', HolidayController::class)->only(['index', 'store']);
    });

    /*
    |--------------------------------------------------------------------------
    | Document Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::post('/', [DocumentController::class, 'store']);
        Route::get('{document}', [DocumentController::class, 'show']);
        Route::get('{document}/download', [DocumentController::class, 'download']);
        Route::delete('{document}', [DocumentController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | RBAC Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('rbac')->group(function () {
        // Roles
        Route::apiResource('roles', RoleController::class);

        // Permissions
        Route::apiResource('permissions', PermissionController::class);

        // Role Permissions
        Route::prefix('roles/{role}')->group(function () {
            Route::post('permissions', [RoleController::class, 'assignPermission']);
            Route::delete('permissions/{permission}', [RoleController::class, 'revokePermission']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Example Protected Routes with Specific Permissions
|--------------------------------------------------------------------------
*/
Route::middleware(['jwt.csrf', 'role:admin'])->get('/admin-only', function () {
    return response()->json(['message' => 'Admin access granted']);
});

Route::middleware(['jwt.csrf', 'permission:edit_student_records'])->put('/students/{id}', function () {
    return response()->json(['message' => 'Student record updated']);
});
