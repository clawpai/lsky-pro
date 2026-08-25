<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Album;
use App\Models\Like;
use App\Models\Report;
use App\Models\Share;
use App\Models\User;
use App\PhotoStatus;
use App\ReportStatus;
use App\ShareType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ShareService
{
    /**
     * 分享详情
     */
    public function show(string $slug): Share
    {
        /** @var Share $share */
        $share = Share::with(['albums', 'user'])
            ->withCount('likes as like_count')
            ->where('slug', $slug)
            ->firstOrFail();

        /** @var User $user */
        $user = Auth::guard('sanctum')->user();

        // 当前用户是否点赞了
        $share->is_liked = !is_null($user) && $share->likes()->where('user_id', $user->id)->exists();

        $share->increment('view_count');

        return $share;
    }

    /**
     * 举报分享
     */
    public function report(string $slug, array $data): Report
    {
        /** @var Share $share */
        $share = Share::where('slug', $slug)->firstOrFail();

        /** @var Report $report */
        $report = $share->reports()->create([...$data, ...[
            'report_user_id' => $share->user_id,
            'status' => ReportStatus::Unhandled,
        ]]);

        return $report;
    }

    /**
     * 点赞分享
     */
    public function like(string $slug): Like
    {
        /** @var Share $share */
        $share = Share::where('slug', $slug)->firstOrFail();

        /** @var Like $like */
        $like = $share->likes()->firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        return $like;
    }

    /**
     * 取消点赞分享
     */
    public function unlike(string $slug): bool
    {
        /** @var Share $share */
        $share = Share::where('slug', $slug)->firstOrFail();

        return $share->likes()->where('user_id', Auth::id())->delete() > 0;
    }

    /**
     * 图片列表
     *
     * @param Share $share
     * @param array $queries
     * @return LengthAwarePaginator
     */
    public function photos(Share $share, array $queries = []): LengthAwarePaginator
    {
        if ($share->type == ShareType::Album) {
            /** @var Album $album */
            $album = $share->albums->first();
            // 直接获取相册中的图片
            $query = $album->photos();
        } else {
            // 获取分享的图片
            $query = $share->photos();
        }

        return $query->withCount('likes as like_count')
            ->with(['tags', 'user', 'storage'])
            ->has('user')
            ->where('status', PhotoStatus::Normal)
            ->paginate(data_get($queries, 'per_page', 40));
    }
}