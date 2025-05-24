<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::where('CDC_FLAG', 'A')->get();
        return response()->json($permissions);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'permission_name' => 'required|string|unique:permissions',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $permission = Permission::create([
            'permission_name' => $request->permission_name,
            'description' => $request->description,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($permission, 201);
    }

    public function show($id)
    {
        $permission = Permission::with(['roles'])
            ->where('permission_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($permission);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::where('permission_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'permission_name' => 'sometimes|string|unique:permissions,permission_name,'.$id.',permission_id',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create new version
        $newPermission = $permission->replicate();
        $newPermission->fill($request->all());
        $newPermission->save();

        // Invalidate old version
        $permission->update([
            'CDC_FLAG' => 'I',
            'valid_to' => now()
        ]);

        return response()->json($newPermission);
    }

    public function destroy($id)
    {
        $permission = Permission::where('permission_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $permission->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }
}