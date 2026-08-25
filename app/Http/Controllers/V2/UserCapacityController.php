<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserCapacityService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Models\UserCapacity;
use App\Support\R;
use Symfony\Component\HttpFoundation\Response;

class UserCapacityController extends Controller
{
    /**
     * 用户拥有的容量
     */
    public function index(QueryRequest $request): Response
    {
        $capacities = UserCapacityService::paginate($request->validated());

        $capacities->getCollection()->each(function (UserCapacity $userCapacity) {
            $userCapacity->setVisible(['id', 'from', 'capacity', 'expired_at', 'created_at']);
        });

        return R::success(data: $capacities);
    }

    /**
     * 删除订阅
     */
    public function destroy(string $id): Response
    {
        UserCapacityService::destroy($id);

        return R::success()->setStatusCode(204);
    }
}
