<?php

namespace App\Http\Controllers\V2;

use App\Facades\AppService;
use App\Facades\ExploreAlbumService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExploreAlbumQueryRequest;
use App\Http\Requests\ExplorePhotoQueryRequest;
use App\Http\Requests\ReportStoreRequest;
use App\Http\Resources\ExploreAlbumResource;
use App\Http\Resources\ExplorePhotoResource;
use App\Support\R;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExploreAlbumController extends Controller
{
    /**
     * 相册列表
     */
    public function albums(ExploreAlbumQueryRequest $request): Response
    {
        $albums = ExploreAlbumService::albums($request->validated());

        return R::success(data: ExploreAlbumResource::collection($albums)->response()->getData());
    }

    /**
     * 指定相册的图片列表
     */
    public function photos(ExplorePhotoQueryRequest $request): Response
    {
        $photos = ExploreAlbumService::photos($request->route('id'), $request->validated());

        return R::success(data: ExplorePhotoResource::collection($photos)->response()->getData());
    }

    /**
     * 相册详情
     */
    public function album(Request $request): Response
    {
        $album = ExploreAlbumService::album((string)$request->route('id'));

        return R::success(data: ExploreAlbumResource::make($album));
    }

    /**
     * 举报相册
     */
    public function report(string $id, ReportStoreRequest $request): Response
    {
        ExploreAlbumService::report($id, [...$request->validated(), ...['ip_address' => AppService::getRequestIp($request)]]);

        return R::success()->setStatusCode(201);
    }

    /**
     * 点赞相册
     */
    public function like(string $id): Response
    {
        ExploreAlbumService::like($id);

        return R::success()->setStatusCode(201);
    }

    /**
     * 取消点赞相册
     */
    public function unlike(string $id): Response
    {
        ExploreAlbumService::unlike($id);

        return R::success()->setStatusCode(204);
    }
}
