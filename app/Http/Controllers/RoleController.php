<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::where('CDC_FLAG', 'A')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|unique:roles',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::create([
            'role_name' => $request->role_name,
            'description' => $request->description,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($role, 201);
    }

    public function show($id)
    {
        $role = Role::with(['permissions'])
            ->where('role_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::where('role_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'role_name' => 'sometimes|string|unique:roles,role_name,'.$id.',role_id',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create new version
        $newRole = $role->replicate();
        $newRole->fill($request->all());
        $newRole->save();

        // Invalidate old version
        $role->update([
            'CDC_FLAG' => 'I',
            'valid_to' => now()
        ]);

        return response()->json($newRole);
    }

    public function destroy($id)
    {
        $role = Role::where('role_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $role->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }

    public function assignPermission(Request $request, $roleId)
    {
        $validator = Validator::make($request->all(), [
            'permission_id' => 'required|exists:permissions,permission_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::where('role_id', $roleId)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        // Check if permission already assigned
        if ($role->permissions()->where('permission_id', $request->permission_id)->exists()) {
            return response()->json(['error' => 'Permission already assigned to this role'], 422);
        }

        $role->permissions()->attach($request->permission_id, [
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json(['message' => 'Permission assigned successfully']);
    }

    public function revokePermission($roleId, $permissionId)
    {
        $role = Role::where('role_id', $roleId)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        // Find the existing assignment
        $assignment = $role->permissions()
            ->where('permission_id', $permissionId)
            ->wherePivot('CDC_FLAG', 'A')
            ->first();

        if (!$assignment) {
            return response()->json(['error' => 'Permission not assigned to this role'], 404);
        }

        // Update the pivot record to mark as inactive
        $role->permissions()
            ->updateExistingPivot($permissionId, [
                'CDC_FLAG' => 'I',
                'valid_to' => now()
            ]);

        return response()->json(['message' => 'Permission revoked successfully']);
    }
}