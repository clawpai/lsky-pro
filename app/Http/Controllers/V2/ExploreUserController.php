<?php

namespace App\Http\Controllers\V2;

use App\Facades\AppService;
use App\Facades\ExploreUserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExploreAlbumQueryRequest;
use App\Http\Requests\ExplorePhotoQueryRequest;
use App\Http\Requests\ReportStoreRequest;
use App\Http\Resources\ExploreAlbumResource;
use App\Http\Resources\ExplorePhotoResource;
use App\Support\R;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExploreUserController extends Controller
{
    /**
     * 用户主页信息
     */
    public function profile(Request $request): Response
    {
        return R::success(data: ExploreUserService::profile($request->route('username')));
    }

    /**
     * 图片列表
     */
    public function photos(ExplorePhotoQueryRequest $request): Response
    {
        $photos = ExploreUserService::photos($request->route('username'), $request->validated());

        return R::success(data: ExplorePhotoResource::collection($photos)->response()->getData());
    }

    /**
     * 相册列表
     */
    public function albums(ExploreAlbumQueryRequest $request): Response
    {
        $albums = ExploreUserService::albums($request->route('username'), $request->validated());

        return R::success(data: ExploreAlbumResource::collection($albums)->response()->getData());
    }

    /**
     * 举报用户
     */
    public function report(string $id, ReportStoreRequest $request): Response
    {
        ExploreUserService::report($id, [...$request->validated(), ...['ip_address' => AppService::getRequestIp($request)]]);

        return R::success()->setStatusCode(201);
    }
}
