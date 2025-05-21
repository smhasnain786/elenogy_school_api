<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'role_id';
    public $timestamps = false;

    protected $fillable = [
        'role_name', 'description', 'CDC_FLAG', 'valid_from', 'valid_to'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->wherePivot('CDC_FLAG', 'A')
            ->wherePivot('valid_from', '<=', now())
            ->where(function($query) {
                $query->wherePivot('valid_to', '>=', now())
                      ->orWhereNull('valid_to');
            });
    }
}