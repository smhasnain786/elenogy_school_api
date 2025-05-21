<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $primaryKey = 'permission_id';
    public $timestamps = false;

    protected $fillable = [
        'permission_name', 'description', 'CDC_FLAG', 'valid_from', 'valid_to'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id')
            ->wherePivot('CDC_FLAG', 'A')
            ->wherePivot('valid_from', '<=', now())
            ->where(function($query) {
                $query->wherePivot('valid_to', '>=', now())
                      ->orWhereNull('valid_to');
            });
    }
}