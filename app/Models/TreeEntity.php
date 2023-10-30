<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreeEntity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tree_entities';

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    protected $fillable = [
        'id',
        'pid',
        'nodeName',
        'route_name',
        'route_location',
        'icon',
        'status',
        'serials',
        'parents',
        'type',
        'created_by',
        'modified_by',
    ];
        
    public function created_by(){
        return $this->belongsTo(User::class,'created_by','id');
    }
    public function modified_by(){
        return $this->belongsTo(User::class,'modified_by','id');
    }
    
    public function permission()
    {
        return $this->hasMany(RolePermission::class,'view','id');
    }

    public function child(){
        $user = Auth::user()->id;
        $user_role = UserRole::where('user_id', $user)->first();
        $profile_id =  $user_role->role_id;
        // $profile_id=1;
        return $this->hasMany(TreeEntity::class, 'pid','id')->with([
            'child' => function($q)  use ($profile_id) {
                $q
                ->join(DB::raw('( SELECT
                            `feature_id`,`create`,`view_others`,`edit`,`edit_others`,`delete`,`delete_others`
                            FROM
                                user_role_accesses
                            WHERE
                                role_id = '.$profile_id.')
                        t1'), 
                    function($join)
                    {
                       $join->on('tree_entities.id', '=', 't1.feature_id');
                       $join->orOn('tree_entities.id', '=', 't1.create');
                       $join->orOn('tree_entities.id', '=', 't1.view_others');
                       $join->orOn('tree_entities.id', '=', 't1.edit');
                       $join->orOn('tree_entities.id', '=', 't1.edit_others');
                       $join->orOn('tree_entities.id', '=', 't1.delete');
                       $join->orOn('tree_entities.id', '=', 't1.delete_others');
                    });
            }
        ])->select('id','pid','nodeName as title','route_name as href','icon as icon','feature_id','view_others','create','edit','edit_others','delete','delete_others')->orderBy('serials');
    }
    public function menus(){
        return $this->hasMany(TreeEntity::class, 'pid','id')->with('menus')->orderBy('serials')->select('id','nodeName','pid','route_name','serials','status','icon');
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
}