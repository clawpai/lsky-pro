<?php

namespace App\Http\Controllers\V2;

use App\Facades\AppService;
use App\Facades\ExplorePhotoService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExplorePhotoQueryRequest;
use App\Http\Requests\ReportStoreRequest;
use App\Http\Resources\ExplorePhotoResource;
use App\Support\R;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExplorePhotoController extends Controller
{
    /**
     * 图片列表
     */
    public function photos(ExplorePhotoQueryRequest $request): Response
    {
        $photos = ExplorePhotoService::photos($request->validated());

        return R::success(data: ExplorePhotoResource::collection($photos)->response()->getData());
    }

    /**
     * 图片详情
     */
    public function photo(Request $request): Response
    {
        $photo = ExplorePhotoService::photo((string)$request->route('id'));

        return R::success(data: ExplorePhotoResource::make($photo));
    }

    /**
     * 举报图片
     */
    public function report(string $id, ReportStoreRequest $request): Response
    {
        ExplorePhotoService::report($id, [...$request->validated(), ...['ip_address' => AppService::getRequestIp($request)]]);

        return R::success()->setStatusCode(201);
    }

    /**
     * 点赞图片
     */
    public function like(string $id): Response
    {
        ExplorePhotoService::like($id);

        return R::success()->setStatusCode(201);
    }

    /**
     * 取消点赞图片
     */
    public function unlike(string $id): Response
    {
        ExplorePhotoService::unlike($id);

        return R::success()->setStatusCode(204);
    }
}
