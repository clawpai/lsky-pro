<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserShareService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Http\Requests\ShareStoreRequest;
use App\Http\Requests\ShareUpdateRequest;
use App\Http\Resources\UserShareResource;
use App\Support\R;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class UserShareController extends Controller
{
    /**
     * 分享列表
     */
    public function index(QueryRequest $request): Response
    {
        $shares = UserShareService::paginate($request->validated());

        return R::success(data: UserShareResource::collection($shares)->response()->getData());
    }

    /**
     * 创建分享
     */
    public function store(ShareStoreRequest $request): Response
    {
        $data = array_filter(Arr::only($request->validated(), ['type', 'content', 'password', 'expired_at']));
        $share = UserShareService::store($data, $request->validated('ids'));
        return R::success(data: $share->setVisible(['id', 'slug']))->setStatusCode(201);
    }

    /**
     * 分享详情
     */
    public function show(string $id): Response
    {
        $share = UserShareService::show($id);
        return R::success(data: UserShareResource::make($share));
    }

    /**
     * 修改分享
     */
    public function update(ShareUpdateRequest $request, string $id): Response
    {
        UserShareService::update($id, $request->validated());
        return R::success()->setStatusCode(204);
    }

    /**
     * 删除分享
     */
    public function destroy(Request $request): Response
    {
        UserShareService::destroy($request->all());
        return R::success()->setStatusCode(204);
    }
}
