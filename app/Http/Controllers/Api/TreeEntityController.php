<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\TreeEntity\StoreTreeEntityRequest;
use App\Http\Requests\TreeEntity\UpdateTreeEntityRequest;
use App\Models\TreeEntity;
use App\Http\Resources\Resource;
use App\Http\Traits\Access;
use App\Http\Traits\HttpResponses;
use App\Http\Traits\Helper;
use App\Models\UserRole;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreeEntityController extends Controller
{
    use Access;
    use HttpResponses;
    use Helper;
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return TreeEntityCollection
     */
    public function index(Request $request): JsonResponse
    {
        $permission = $this->hasrolePermition($request, 'view');
        if ($permission->status == 'false') {
            return  $this->error('', Constants::ACCESSERROR, 404, false);
        }
        $limit = $request->has('limit') ? $request->limit : 15;
        $data = TreeEntity::paginate($limit);
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
     * @return TreeEntityResource
     */
    public function store(StoreTreeEntityRequest $request): JsonResponse
    {
        try {
            $permission = $this->hasrolePermition($request, 'add');
            if ($permission->status == 'false') {
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            $data = TreeEntity::create($request->all());
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
     * @param TreeEntity $menu_management
     * @return TreeEntityResource
     */
    public function show(TreeEntity $menu_management, Request $request): JsonResponse
    {
        try {
            $permission = $this->hasrolePermition($request, 'edit');
            if ($permission->status == 'false') {
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            if ($menu_management) {
                return $this->success(new Resource($menu_management), Constants::GETALL, 200, true);
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
     * @param TreeEntity $menu_management
     * @return TreeEntityResource
     */
    public function update(UpdateTreeEntityRequest $request, TreeEntity $menu_management)
    {
        try {
            $permission = $this->hasrolePermition($request, 'edit');
            if ($permission->status == 'false') {
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            $menu_management->update($request->all());
            return $this->success(new Resource($menu_management), Constants::UPDATE, 201, true);
        } catch (Exception $exception) {
            return $this->error('', $exception->getMessage(), 404, false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TreeEntity $menu_management
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(TreeEntity $menu_management, Request $request): JsonResponse
    {
        try {
            $permission = $this->hasrolePermition($request, 'delete');
            if ($permission->status == 'false') {
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            $this->recursiveDelete($menu_management->id, $request->status);
            return $this->success('', Constants::DESTROY, 200, true);
        } catch (Exception $exception) {
            return $this->error('', $exception->getMessage(), 404, false);
        }
    }


    /**
     * Update serial and pid resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatemenu(Request $request)
    {
        try {
            $permission = $this->hasrolePermition($request, 'edit');
            if ($permission->status == 'false') {
                return  $this->error('', Constants::ACCESSERROR, 404, false);
            }
            $jdata = $request->all();
            $readbleArray = $this->parseJsonArray($jdata, $pid = 0);
            $i = 0;
            foreach ($readbleArray as $row) {
                $i++;
                $treeentry = TreeEntity::find($row['id']);
                $treeentry['pid'] = $row['pid'];
                $treeentry['serials'] = $i;
                $treeentry->save();
            }
            return response()->json(["status" => "success", "error" => false, "message" => "Success! menu updated."], 201);
        } catch (Exception $exception) {
            return response()->json(["status" => "failed", "error" => $exception->getMessage()], 404);
        }
    }

    /**
     * Create menu  from storage.
     *  
     * @return \Illuminate\Http\Response
     */
    public function treemenu()
    {
        $user = Auth::user();
        $profile_id = $user->role_id;
        $treeentrys = TreeEntity::join(
            DB::raw('( SELECT
                            role_id,feature_id,create,view_others,edit,edit_others,delete,delete_others
                        FROM
                            user_role_accesses
                        WHERE
                            role_id = ' . $profile_id . ')
                               t1'),
            function ($join) {
                $join->on('tree_entities.id', '=', 't1.feature_id');
            }
        )
            ->where('pid', 0)
            ->where('status', '=', 1)
            ->orderBy('serials')
            ->with([
                'children' => function ($q)  use ($profile_id) {
                    $q
                        ->join(
                            DB::s('( SELECT
                                role_id,feature_id,create,view_others,edit,edit_others,delete,delete_others
                                            FROM
                                                user_role_accesses
                                            WHERE
                                                role_id = ' . $profile_id . ')
                                           t1'),
                            function ($join) {
                                $join->on('tree_entities.id',   '=', 't1.feature_id');
                                $join->orOn('tree_entities.id', '=', 't1.create');
                                $join->orOn('tree_entities.id', '=', 't1.view_others');
                                $join->orOn('tree_entities.id', '=', 't1.edit_others');
                                $join->orOn('tree_entities.id', '=', 't1.delete');
                                $join->orOn('tree_entities.id', '=', 't1.delete_others');
                            }
                        );
                }
            ])
            ->get();
        $data = Resource::collection($treeentrys);
        if ($data) {
            return $this->success($data, Constants::GETALL, 200, true);
        } else {
            return $this->error('', Constants::NODATA, 404, false);
        }
    }
    /**
     * Create menu new  from storage.
     *  
     * @return \Illuminate\Http\Response
     */
    public function treemenu_new()
    {
        $user = Auth::user();
        if ($user != '') {
            $user = Auth::user()->id;
            $user_role = UserRole::where('user_id', $user)->first();
            $profile_id =  $user_role->role_id;
            // $profile_id=1;

            $treeentrys = TreeEntity::join(
                DB::raw('( SELECT
                                `role_id`,`feature_id`,`create`,`view_others`,`edit`,`edit_others`,`delete`,`delete_others`
                                FROM
                                    user_role_accesses
                                WHERE
                                    role_id  = ' . $profile_id . ')
                               t1'),
                function ($join) {
                    $join->on('tree_entities.id', '=', 't1.feature_id');
                }
            )
                ->select('id', 'pid', 'nodeName as title', 'icon as icon', 'feature_id', 'create', 'view_others', 'edit', 'edit_others', 'delete', 'delete_others')
                ->where('pid', 0)
                ->where('status', '=', 1)
                ->orderBy('serials')
                ->with([
                    'child' => function ($q)  use ($profile_id) {
                        $q
                            ->join(
                                DB::raw('( SELECT
                                            `role_id`,`feature_id`,`create`,`view_others`,`edit`,`edit_others`,`delete`,`delete_others`
                                            FROM
                                                user_role_accesses
                                            WHERE
                                                role_id  = ' . $profile_id . ')
                                           t1'),
                                function ($join) {
                                    $join->on('tree_entities.id',   '=', 't1.feature_id');
                                    $join->orOn('tree_entities.id', '=', 't1.create');
                                    $join->orOn('tree_entities.id', '=', 't1.view_others');
                                    $join->orOn('tree_entities.id', '=', 't1.edit_others');
                                    $join->orOn('tree_entities.id', '=', 't1.delete');
                                    $join->orOn('tree_entities.id', '=', 't1.delete_others');
                                }
                            );
                    }
                ])
                ->get();
        }
        
        
        if ($treeentrys) {
            return $this->success($treeentrys, Constants::GETALL, 200, true);
        } else {
            return $this->error('', Constants::NODATA, 404, false);
        }
    }


    public function  buildmenu()
    {
        $treeentrys = TreeEntity::with('menus')
            ->where('pid', 0)
            ->orderBy('serials')->select('id', 'nodeName', 'pid', 'route_name', 'serials', 'status', 'icon')->get();
        if ($treeentrys) {
            return $this->success(Resource::collection($treeentrys), Constants::GETALL, 200, true);
        } else {
            return $this->error('', Constants::NODATA, 404, false);
        }
    }
}
