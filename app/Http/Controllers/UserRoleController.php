<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends Controller
{
    public function index($userId)
    {
        $user = User::with(['roles'])
            ->where('user_id', $userId)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($user->roles);
    }

    public function store(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,role_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('user_id', $userId)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        // Check if role already assigned
        if ($user->roles()->where('role_id', $request->role_id)->exists()) {
            return response()->json(['error' => 'Role already assigned to this user'], 422);
        }

        $user->roles()->attach($request->role_id, [
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json(['message' => 'Role assigned successfully']);
    }

    public function destroy($userId, $roleId)
    {
        $user = User::where('user_id', $userId)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        // Find the existing assignment
        $assignment = $user->roles()
            ->where('role_id', $roleId)
            ->wherePivot('CDC_FLAG', 'A')
            ->first();

        if (!$assignment) {
            return response()->json(['error' => 'Role not assigned to this user'], 404);
        }

        // Update the pivot record to mark as inactive
        $user->roles()
            ->updateExistingPivot($roleId, [
                'CDC_FLAG' => 'I',
                'valid_to' => now()
            ]);

        return response()->json(['message' => 'Role revoked successfully']);
    }
}