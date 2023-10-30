<?php

namespace App\Http\Traits;

use App\Constants\Constants;
use App\Models\TreeEntity;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait Helper
{
  protected function bind_to_template($replacements, $template)
  {
    return preg_replace_callback('/{{(.+?)}}/', function ($matches) use ($replacements) {
      return array_key_exists($matches[1], $replacements) ? $replacements[$matches[1]] : null;
      // return $replacements[$matches[1]] ? $replacements[$matches[1]]:null;
    }, $template);
  }
  protected function pick($table, $field, $cond, $val)
  {
    $tt = NULL;
    if ($cond == null) {
      $query = DB::table($table)
        ->select(DB::raw($field . ' AS name'));
    } else {
      $query = DB::table($table)
        ->select(DB::raw($field . ' AS name'));
      $query->where($cond, $val);
    }
    $data = $query->get();
    foreach ($data as $index => $da) {
      if ($tt == null) {
        $tt = $da->name;
      } else {
        $tt = $tt . '<BR>' . $da->name;
      }
    }
    return $tt;
  }

  protected function parseJsonArray($jsonArray, $pid = 0)
  {
    $return = array();
    foreach ($jsonArray as $subArray) {
      $returnSubSubArray = array();
      if (isset($subArray['menus'])) {
        $returnSubSubArray = $this->parseJsonArray($subArray['menus'], $subArray['id']);
      }
      $return[] = array('id' => $subArray['id'], 'pid' => $pid);
      $return = array_merge($return, $returnSubSubArray);
    }
    return $return;
  }

  protected function recursiveDelete($id, $status)
  {
    $query = TreeEntity::where('pid', '=', $id)->get();
    if ($query->count() > 0) {
      foreach ($query as $key => $value) {
        $this->recursiveDelete($value->id, $status);
      }
    }
    $treeentry = TreeEntity::find($id);
    $treeentry['status'] = $status;
    $treeentry->save();
  }

  protected function hasrolePermition($request,$type)
  {
    $currenturl = $request->segment(2);
    $user=Auth::user()->id;   
    $user_role=UserRole::where('user_id',$user)->first();   
    $permission =(object)[];
    if ($currenturl != '' && $user_role) {
      $permission = DB::table('tree_entities')      
        ->select('nodeName', 'user_role_accesses.feature_id', 'user_role_accesses.create', 'user_role_accesses.view_others','user_role_accesses.edit','user_role_accesses.edit_others','user_role_accesses.delete','user_role_accesses.delete_others')
        ->join('user_role_accesses', 'user_role_accesses.feature_id', '=', 'tree_entities.id')
        ->where('tree_entities.route_name', 'Like', '%' . $currenturl)
        ->where('user_role_accesses.role_id', $user_role->role_id)
        ->first();
        
      if($permission){
        $permission->status='true';
        $haspermission = json_decode(json_encode($permission),true);
        switch ($type) {
          case "view":
            if($haspermission['feature_id']>0){
              return  $permission; 
            }else{
              return $permission->status='false';
            }
            break;
          case "add":
            if($haspermission['create']>0){
              return  $permission; 
            }else{
              return $permission->status='false';
            }
            break;
          case "show":
            if($haspermission['edit']>0){
              return  $permission; 
            }else{
              return $permission->status='false';
            }
            break;
          case "edit":
            if($haspermission['edit']>0){
              return  $permission; 
            }else{
              return $permission->status='false';
            }
            break;
          case "delete":
            if($haspermission['delete']>0){
              return  $permission; 
            }else{
              return $permission->status='false';
            }
            break;
          default:
            return $permission->status='false';
        }
      }else{
        $permission =(object)[];
        $permission->status='false';       
        return $permission;        
      }      
    } else {
       return $permission->status='false';
    }
  }

  protected function is_base64($s)
  {
    if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) return false;
    $decoded = base64_decode($s, true);
    if (false === $decoded) return false;
    if (base64_encode($decoded) != $s) return false;
    return true;
  }

  
}
