<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Constants\AuthConstants;
use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleAccess\StoreUserRoleAccessRequest;
use App\Http\Requests\UserRoleAccess\UpdateUserRoleAccessRequest;
use App\Models\UserRoleAccess;
use App\Http\Resources\Resource;
use App\Http\Traits\Access;
use App\Http\Traits\HttpResponses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserRoleAccessController extends Controller
{
    use Access;
    use HttpResponses;

    /**
     * Display the specified resource.
     *
     * @param UserRoleAccess $userRoleAccess
     * @return UserRoleAccessResource
     */
    public function show(UserRoleAccess $userRoleAccess): JsonResponse
    {
        try {
            if ($userRoleAccess) {
                $profilePermission = DB::select("SELECT   
                        id,
                        pid,
                        nodeName,
                        (SELECT Count(pid)
                        FROM   tree_entities AS test
                        WHERE  test.pid = treeNode.id
                        AND test.nodeName <> '') AS haschild, 
                        id as `view`,
                        (SELECT tree_entities.`id` FROM `tree_entities` WHERE `parents`= treeNode.id AND `type`=1 )`add`, 
                        (SELECT tree_entities.`id` FROM `tree_entities` WHERE `parents`= treeNode.id AND `type`=2 )`edit`,
                    id as `delete` ,
                    per.*
                    FROM
                        `tree_entities` as treeNode
                        LEFT JOIN(
                            SELECT feature_id AS sid,
                                IFNULL(`feature_id`, 0 ) as `viewP` ,
                                IFNULL(`create`, 0 ) as `createP`,
                                IFNULL(`view_others`, 0 ) as `view_othersP`,
                                IFNULL(`edit`, 0 )as `editP`,
                                IFNULL(`edit_others`, 0 )as `edit_othersP`,
                                IFNULL(`delete`, 0 )as `deleteP`,
                                IFNULL(`delete_others`, 0 )as `delete_othersP`
                                FROM   user_role_accesses
                                WHERE  role_id = $userRoleAccess->id) AS per
                                ON per.sid = treeNode.id
                    WHERE treeNode.`status_id`=1
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
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param UserRoleAccess $userRoleAccess
     * @return UserRoleAccessResource
     */
    public function update(UpdateUserRoleAccessRequest $request, UserRoleAccess $userRoleAccess)
    {
        try {
            $userRoleAccess->update($request->all());
            return $this->success(new Resource($userRoleAccess), Constants::UPDATE, 201, true);
        } catch (Exception $exception) {
            return $this->error('', $exception->getMessage(), 404, false);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UserRoleAccess $userRoleAccess
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(UserRoleAccess $userRoleAccess): JsonResponse
    {
        try {
            $userRoleAccess->delete();
            return $this->success('', Constants::DESTROY, 200, true);
        } catch (Exception $exception) {
            return $this->error('', $exception->getMessage(), 404, false);
        }
    }
}
