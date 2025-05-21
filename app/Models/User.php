<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'email', 'password', 'user_type', 'school_id', 'CDC_FLAG', 'valid_from', 'valid_to'
    ];

    protected $hidden = [
        'password'
    ];

    // Get the identifier that will be stored in the subject claim of the JWT.
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Return a key value array, containing any custom claims to be added to the JWT.
    public function getJWTCustomClaims()
    {
        return [
            'user_type' => $this->user_type,
            'school_id' => $this->school_id,
            'roles' => $this->roles()->pluck('role_name')->toArray()
        ];
    }

    // Relationship to roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->wherePivot('CDC_FLAG', 'A')
            ->wherePivot('valid_from', '<=', now())
            ->where(function($query) {
                $query->wherePivot('valid_to', '>=', now())
                      ->orWhereNull('valid_to');
            });
    }

    // Check if user has a role
    public function hasRole($role)
    {
        return $this->roles()->where('role_name', $role)->exists();
    }

    // Check if user has any of the given roles
    public function hasAnyRole($roles)
    {
        return $this->roles()->whereIn('role_name', (array)$roles)->exists();
    }

    // Check if user has a permission
    public function hasPermission($permission)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('permission_name', $permission)->exists()) {
                return true;
            }
        }
        return false;
    }
}