<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['jwt_csrf'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Example protected route with role check
Route::middleware(['jwt_csrf', 'role:admin'])->get('/admin-only', function () {
    return response()->json(['message' => 'Admin access granted']);
});

// Example protected route with permission check
Route::middleware(['jwt_csrf', 'permission:edit_student_records'])->put('/students/{id}', function () {
    return response()->json(['message' => 'Student record updated']);
});
