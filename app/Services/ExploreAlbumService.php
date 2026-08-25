<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Album;
use App\Models\Like;
use App\Models\Report;
use App\ReportStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ExploreAlbumService
{
    /**
     * 相册列表
     *
     * @param array $queries 筛选条件
     * @return LengthAwarePaginator
     */
    public function albums(array $queries = []): LengthAwarePaginator
    {
        return Album::explore($queries)->paginate($queries['per_page'] ?? 20);
    }

    /**
     * 获取指定相册中的图片列表
     * @param string $id 相册ID
     * @param array $queries 筛选条件
     * @return LengthAwarePaginator
     */
    public function photos(string $id, array $queries = []): LengthAwarePaginator
    {
        $album = $this->album($id);

        return $album->photos()->explore()->paginate($queries['per_page'] ?? 20);
    }

    /**
     * 相册详情
     */
    public function album(string $id): Album
    {
        return Album::explore()->findOrFail($id);
    }

    /**
     * 举报相册
     */
    public function report(string $id, array $data): Report
    {
        $album = $this->album($id);

        /** @var Report $report */
        $report = $album->reports()->create([...$data, ...[
            'report_user_id' => $album->user_id,
            'status' => ReportStatus::Unhandled,
        ]]);

        return $report;
    }

    /**
     * 点赞相册
     */
    public function like(string $id): Like
    {
        $album = $this->album($id);

        /** @var Like $like */
        $like = $album->likes()->firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        return $like;
    }

    /**
     * 取消点赞相册
     */
    public function unlike(string $id): bool
    {
        $album = $this->album($id);

        return $album->likes()->where('user_id', Auth::id())->delete() > 0;
    }
}