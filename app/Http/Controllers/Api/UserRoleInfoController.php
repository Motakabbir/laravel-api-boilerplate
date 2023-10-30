<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleAccess\UpdateUserRoleAccessRequest;
use App\Http\Requests\UserRoleInfo\StoreUserRoleInfoRequest;
use App\Http\Requests\UserRoleInfo\UpdateUserRoleInfoRequest;
use App\Models\UserRoleInfo;
use App\Http\Resources\Resource;
use App\Http\Traits\Access;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Models\UserRoleAccess;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserRoleInfoController extends Controller
{
    use Access;
    use HttpResponses;
    use Helper;
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return UserRoleInfoCollection
     */
    public function index(Request $request)
    {
        $permission= $this->hasrolePermition($request,'view');      
        if($permission->status=='false'){
           return  $this->error('', Constants::ACCESSERROR, 404, false);
        }
        $limit = $request->has('limit') ? $request->limit : 15;        
        $data = UserRoleInfo::orderByName()->filter($request->all(),$permission)
        ->paginate($limit);
        $collectionData = Resource::collection($data)->response()->getData();
        if ($collectionData) {
            return $this->success($collectionData, Constants::GETALL, 200, true);
        } else {
            return $this->error('', Constants::NODATA, 404, false);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return UserRoleInfoResource
     */
    public function store(StoreUserRoleInfoRequest $request): JsonResponse
    {
        try {
            $permission= $this->hasrolePermition($request,'add');      
            if($permission->status=='false'){
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            $data = UserRoleInfo::create($request->all());
            return $this->success(
                new Resource($data),
                Constants::STORE,
                201,
                true
            );
        } catch (Exception $exception) {
            return $this->error('', Constants::FAILSTORE, 404, false);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param UserRoleInfo $user_role_info
     * @return UserRoleInfoResource
     */
    public function show(UserRoleInfo $user_role_info,Request $request): JsonResponse
    {
        try {
            $permission= $this->hasrolePermition($request,'edit');      
            if($permission->status=='false'){
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            if ($user_role_info) {
                return $this->success(new Resource($user_role_info), Constants::GETALL, 200, true);
            } else {
                return $this->error('', Constants::NODATA, 404, false);
            }
        } catch (\Throwable $th) {
            return $this->error('', $th, 404, false);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param UserRoleInfo $user_role_info
     * @return UserRoleInfoResource
     */
    public function update(UpdateUserRoleInfoRequest $request, UserRoleInfo $user_role_info)
    {
        try {
            $permission= $this->hasrolePermition($request,'edit');      
            if($permission->status=='false'){
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            $user_role_info->update($request->all());
            return $this->success(new Resource($user_role_info), Constants::UPDATE, 201, true);
        } catch (Exception $exception) {
            return $this->error('', $exception->getMessage(), 404, false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UserRoleInfo $user_role_info
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(UserRoleInfo $user_role_info,Request $request): JsonResponse
    {
        try {
            $permission= $this->hasrolePermition($request,'delete');      
            if($permission->status=='false'){
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            $user_role_info->delete();
            return $this->success('', Constants::DESTROY, 200, true);
        } catch (Exception $exception) {
            return $this->error('', $exception->getMessage(), 404, false);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function permissionShow(UserRoleInfo $user_role_info,Request $request)
    {
        try {
            $permission= $this->hasrolePermition($request,'edit');      
            if($permission->status=='false'){
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            if ($user_role_info) {
                $user_role = Auth::user()->user_role_id;
                $profilePermission = DB::select("SELECT   
                        id,
                        pid,
                        nodeName,
                        (SELECT Count(pid)
                        FROM   tree_entities AS test
                        WHERE  test.pid = treeNode.id
                        AND test.nodeName <> '') AS haschild,
                        per.*
                    FROM
                        `tree_entities` as treeNode
                        LEFT JOIN(SELECT 
                            feature_id AS sid,
                            IFNULL(`feature_id`, 0 ) as `viewP` ,
                            IFNULL(`create`, 0 ) as `createP`,
                            IFNULL(`view_others`, 0 ) as `view_othersP`,
                            IFNULL(`edit`, 0 )as `editP`,
                            IFNULL(`edit_others`, 0 )as `edit_othersP`,
                            IFNULL(`delete`, 0 )as `deleteP`,
                            IFNULL(`delete_others`, 0 )as `delete_othersP`
                        FROM   user_role_accesses
                            WHERE  role_id =  $user_role_info->id) AS per
                            ON per.sid = treeNode.id 
                        WHERE treeNode.`status`=1
                        ORDER  BY treeNode.serials");
                return $this->success(new Resource($profilePermission), Constants::GETALL, 200, true);
            } else {
                return $this->error('', Constants::NODATA, 404, false);
            }
        } catch (\Throwable $th) {
            return $this->error('', $th, 404, false);
        }
    }

    /**
     * Permission updatw.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pupdate(UpdateUserRoleAccessRequest $request, UserRoleAccess $userRoleAccess)
    {
        try {
            $permission= $this->hasrolePermition($request,'edit');      
            if($permission->status=='false'){
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            $roleid = 0;
            foreach ($request->all() as $value) {
                if ($roleid == 0) {
                    $roleid = $value['role_id'];
                }
            }
            DB::table('user_role_accesses')
                ->where('role_id', $roleid)
                ->delete();
            foreach ($request->all() as $value) {
                $pp = UserRoleAccess::create([
                    "role_id" => $value['role_id'],
                    "feature_id" => $value['viewP'] ?? 0,
                    "create" => $value['addP'] ?? 0,
                    "edit" => $value['editP'] ?? 0,
                    "delete" => $value['deleteP'] ?? 0,
                    "user_id" =>  Auth::user()->id
                ]);
            }
            return $this->success('', Constants::UPDATE, 201, true);
        } catch (\Exception $exception) {
            return $this->error('', $exception->getMessage(), 404, false);
        }
    }
}
