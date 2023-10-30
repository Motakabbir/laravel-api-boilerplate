<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use HasFactory;   

    public function RoleDtlInfo(){
        return $this->belongsTo(UserRoleInfo::class,'role_id');
    }

    public function RoleAccesses(){
        return $this->hasMany(UserRoleAccess::class,'role_id','role_id');
    }
}
