<?php

namespace App\Http\Controllers\V2;

use App\Facades\AppService;
use App\Facades\ShareService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Http\Requests\ReportStoreRequest;
use App\Http\Resources\ExplorePhotoResource;
use App\Models\Album;
use App\Models\Share;
use App\Support\R;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShareController extends Controller
{
    /**
     * 举报分享
     */
    public function report(string $slug, ReportStoreRequest $request): Response
    {
        ShareService::report($slug, [
            ...$request->validated(),
            ...['ip_address' => AppService::getRequestIp($request)]
        ]);

        return R::success()->setStatusCode(201);
    }

    /**
     * 点赞分享
     */
    public function like(string $slug): Response
    {
        ShareService::like($slug);

        return R::success()->setStatusCode(201);
    }

    /**
     * 取消点赞分享
     */
    public function unlike(string $slug): Response
    {
        ShareService::unlike($slug);

        return R::success()->setStatusCode(204);
    }

    /**
     * 图片列表
     */
    public function photos(string $slug, QueryRequest $request): Response
    {
        $share = ShareService::show($slug);

        if ($share->expired_at && $share->expired_at->isPast()) {
            return R::error('分享已过期')->setStatusCode(404);
        }

        if (!$this->verifyPassword($share, (string)$request->query('password'))) {
            return R::success(data: ['is_valid' => false]);
        }

        $photos = ShareService::photos($share, $request->validated());

        return R::success(data: ExplorePhotoResource::collection($photos)->additional([
            'is_valid' => true,
        ])->response()->getData());
    }

    /**
     * 获取分享
     */
    public function show(string $slug, Request $request): Response
    {
        $share = ShareService::show($slug);

        if ($share->expired_at && $share->expired_at->isPast()) {
            return R::error('分享已过期')->setStatusCode(404);
        }

        if (!$this->verifyPassword($share, (string)$request->query('password'))) {
            return R::success(data: ['is_valid' => false]);
        }

        $share->user->append('avatar_url')->setVisible(['id', 'avatar_url', 'username', 'name', 'is_admin']);

        $share->setVisible([
            'id', 'type', 'slug', 'content', 'album', 'user', 'is_valid',
            'view_count', 'like_count', 'is_liked', 'expired_at', 'created_at',
        ]);

        /** @var Album $album */
        $album = $share->albums->first();
        if (!is_null($album)) {
            $share->album = $album->setVisible(['id', 'name', 'intro']);
        }

        $share->is_valid = true;

        return R::success(data: $share);
    }

    protected function verifyPassword(Share $share, string $password): bool
    {
        if ($share->password && $password !== $share->password) {
            return false;
        }

        return true;
    }
}
