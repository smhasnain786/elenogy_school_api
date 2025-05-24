<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\CsrfToken;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Permission;
use App\Models\School;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|in:student,parent,teacher,staff',
            'school_id' => 'required|exists:schools,school_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'school_id' => $request->school_id,
            'CDC_FLAG' => 'A',
            'valid_from' => now(),
            'created_at' => now()
        ]);

        // Assign default role based on user type
        $role = Role::where('role_name', $request->user_type)->first();
        if ($role) {
            $user->roles()->attach($role->role_id);
        }

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Authenticate user and return JWT token
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Generate CSRF token
        $csrfToken = bin2hex(random_bytes(32));
        $hashedToken = Hash::make($csrfToken);

        // Store CSRF token in database
        CsrfToken::create([
            'user_id' => JWTAuth::user()->user_id,
            'token' => $hashedToken,
            'expires_at' => now()->addHours(2)
        ]);

        return $this->respondWithToken($token, $csrfToken);
    }

    /**
     * Logout user (invalidate token)
     */
    public function logout()
    {
        // Get the authenticated user
        $user = JWTAuth::user();
        
        // Invalidate the JWT token
        JWTAuth::invalidate(JWTAuth::getToken());
        
        // Delete all CSRF tokens for this user
        CsrfToken::where('user_id', $user->user_id)->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            
            // Generate new CSRF token
            $csrfToken = Str::random(60);
            $hashedToken = Hash::make($csrfToken);
            
            // Store new CSRF token
            CsrfToken::create([
                'user_id' => JWTAuth::user()->user_id,
                'token' => $hashedToken,
                'expires_at' => now()->addHours(2)
            ]);
            
            return $this->respondWithToken($newToken, $csrfToken);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not refresh token'], 401);
        }
    }

    /**
     * Get authenticated user details
     */
    public function me()
    {
        $user = JWTAuth::user();
        $roles = $user->roles()->pluck('role_name');
        
        return response()->json([
            'user' => $user,
            'roles' => $roles,
            'permissions' => $this->getUserPermissions($user)
        ]);
    }

    /**
     * Helper to format token response
     */
    protected function respondWithToken($token, $csrfToken)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'xsrf_token' => $csrfToken,
            'user' => JWTAuth::user(),
            'roles' => JWTAuth::user()->roles()->pluck('role_name'),
            'permissions' => $this->getUserPermissions(JWTAuth::user())
        ]);
    }

    /**
     * Get all permissions for user
     */
    protected function getUserPermissions($user)
    {
        $permissions = [];
        
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission->permission_name;
            }
        }
        
        return array_unique($permissions);
    }
}