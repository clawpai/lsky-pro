<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserAlbumService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AlbumStoreRequest;
use App\Http\Requests\QueryRequest;
use App\Http\Resources\UserAlbumResource;
use App\Support\R;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAlbumController extends Controller
{
    /**
     * 相册列表
     */
    public function index(QueryRequest $request): Response
    {
        $albums = UserAlbumService::paginate($request->validated());

        return R::success(data: UserAlbumResource::collection($albums)->response()->getData());
    }

    /**
     * 新增相册
     */
    public function store(AlbumStoreRequest $request): Response
    {
        $album = UserAlbumService::store(array_filter($request->validated(), fn ($value) => ! is_null($value)));
        return R::success(data: ['id' => $album->id]);
    }

    /**
     * 相册详情
     */
    public function show(string $id): Response
    {
        $album = UserAlbumService::show($id);
        return R::success(data: UserAlbumResource::make($album));
    }

    /**
     * 修改相册
     */
    public function update(AlbumStoreRequest $request, string $id): Response
    {
        UserAlbumService::update($id, array_filter($request->validated(), fn ($value) => ! is_null($value)));
        return R::success()->setStatusCode(204);
    }

    /**
     * 删除相册
     */
    public function destroy(string $id): Response
    {
        UserAlbumService::destroy($id);
        return R::success()->setStatusCode(204);
    }

    /**
     * 添加图片到相册
     */
    public function addPhotos(string $id, Request $request): Response
    {
        UserAlbumService::addPhotos($id, $request->all());
        return R::success()->setStatusCode(201);
    }

    /**
     * 从相册中移除图片
     */
    public function removePhotos(string $id, Request $request): Response
    {
        UserAlbumService::removePhotos($id, $request->all());
        return R::success()->setStatusCode(204);
    }

    /**
     * 附加标签
     */
    public function attachTags(string $id, Request $request): Response
    {
        UserAlbumService::attachTags($id, $request->all());
        return R::success()->setStatusCode(201);
    }

    /**
     * 移除标签
     */
    public function removeTags(string $id, Request $request): Response
    {
        UserAlbumService::removeTags($id, $request->all());
        return R::success()->setStatusCode(204);
    }
}
