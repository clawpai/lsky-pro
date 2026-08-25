<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserGroupService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Models\UserGroup;
use App\Support\R;
use Symfony\Component\HttpFoundation\Response;

class UserGroupController extends Controller
{
    /**
     * 用户拥有的角色组
     */
    public function index(QueryRequest $request): Response
    {
        $groups = UserGroupService::paginate($request->validated());

        $groups->getCollection()->each(function (UserGroup $userGroup) {
            $userGroup->group->setVisible(['id', 'name', 'intro', 'is_default', 'is_guest', 'options']);
            $userGroup->setVisible(['id', 'from', 'group', 'expired_at', 'created_at']);
        });

        return R::success(data: $groups);
    }

    /**
     * 删除订阅
     */
    public function destroy(string $id): Response
    {
        UserGroupService::destroy($id);

        return R::success()->setStatusCode(204);
    }
}
