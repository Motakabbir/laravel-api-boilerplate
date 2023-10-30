<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRoleAccess extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
            "role_id",
            "feature_id",
            "create",
            "view_others",
            "edit",
            "edit_others",
            "delete",
            "delete_others"
    ];
    
    public function RoleAccesses(){
        return $this->hasMany(UserRoleAccess::class,'role_id');
    }
}
