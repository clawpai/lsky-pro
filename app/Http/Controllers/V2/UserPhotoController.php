<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserPhotoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PhotoUpdateRequest;
use App\Http\Requests\UserPhotoListQueryRequest;
use App\Http\Resources\UserPhotoResource;
use App\Support\R;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserPhotoController extends Controller
{
    /**
     * 图片列表
     */
    public function index(UserPhotoListQueryRequest $request): Response
    {
        $photos = UserPhotoService::paginate($request->validated());

        return R::success(data: UserPhotoResource::collection($photos)->response()->getData());
    }

    /**
     * 获取图片时间线
     */
    public function timeline(Request $request): Response
    {
        $timeline = UserPhotoService::timeline((string)$request->query('q'));
        return R::success(data: ['data' => $timeline]);
    }

    /**
     * 图片详情
     */
    public function show(string $id): Response
    {
        $photo = UserPhotoService::show($id);

        return R::success(data: UserPhotoResource::make($photo));
    }

    /**
     * 修改图片信息
     */
    public function update(PhotoUpdateRequest $request): Response
    {
        if (UserPhotoService::batchUpdate($request->validated())) {
            return R::success()->setStatusCode(204);
        }

        return R::error();
    }

    /**
     * 删除图片
     */
    public function destroy(Request $request): Response
    {
        UserPhotoService::destroy($request->all());
        return R::success()->setStatusCode(204);
    }

    /**
     * 附加标签
     */
    public function attachTags(string $id, Request $request): Response
    {
        UserPhotoService::attachTags($id, $request->all());
        return R::success()->setStatusCode(201);
    }

    /**
     * 移除标签
     */
    public function removeTags(string $id, Request $request): Response
    {
        UserPhotoService::removeTags($id, $request->all());
        return R::success()->setStatusCode(204);
    }
}
