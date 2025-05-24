<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['school', 'roles'])
            ->where('CDC_FLAG', 'A');

        if ($request->has('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $users = $query->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'user_type' => 'required|in:student,parent,teacher,staff',
            'school_id' => 'required|exists:schools,school_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'school_id' => $request->school_id,
            'CDC_FLAG' => 'A',
            'valid_from' => now()
        ]);

        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::with(['school', 'roles.permissions'])
            ->where('user_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();
            
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('user_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:users,email,'.$id.',user_id',
            'password' => 'sometimes|string|min:8',
            'user_type' => 'sometimes|in:student,parent,teacher,staff',
            'school_id' => 'sometimes|exists:schools,school_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        if ($request->has('password')) {
            $data['password_hash'] = Hash::make($request->password);
            unset($data['password']);
        }

        // Create new version
        $newUser = $user->replicate();
        $newUser->fill($data);
        $newUser->save();

        // Invalidate old version
        $user->update([
            'CDC_FLAG' => 'I',
            'valid_to' => now()
        ]);

        return response()->json($newUser);
    }

    public function destroy($id)
    {
        $user = User::where('user_id', $id)
            ->where('CDC_FLAG', 'A')
            ->firstOrFail();

        $user->update([
            'CDC_FLAG' => 'D',
            'valid_to' => now()
        ]);

        return response()->json(null, 204);
    }
}