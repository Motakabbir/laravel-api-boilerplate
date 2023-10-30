<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class UserRoleInfo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'role_title',
        'weight',
        'status',
        'created_by',
        'updated_by',
        'deleted_at'
    ];

    protected $dates = ['deleted_at'];

    public function RoleAccesses(){
        return $this->hasMany(UserRoleAccess::class,'role_id');
    }

    protected static function boot()
    {

        parent::boot();

        // updating created_by and updated_by when model is created
        static::creating(function ($model) {
            if (!$model->isDirty('created_by')) {
                $model->created_by = auth()->user()->id;
            }
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = auth()->user()->id;
            }
        });

        // updating updated_by when model is updated
        static::updating(function ($model) {
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = auth()->user()->id;
            }
        });
    }


    public function scopeOrderByName($query)
    {
        $query->orderBy('role_title');
    }
    
    public function scopeFilter($query, array $filters,$permission)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('role_title', 'like', '%'.$search.'%');
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', '=', $status);
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        })->when($permission->view_others>0, function($query){
            $query->where('created_by',Auth::user()->id);
        });
    }
}
