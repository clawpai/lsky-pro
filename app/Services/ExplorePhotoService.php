<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Like;
use App\Models\Photo;
use App\Models\Report;
use App\ReportStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ExplorePhotoService
{
    /**
     * 图片列表
     *
     * @param array $queries 筛选条件
     * @return LengthAwarePaginator
     */
    public function photos(array $queries = []): LengthAwarePaginator
    {
        return Photo::explore($queries)->paginate($queries['per_page'] ?? 40);
    }

    /**
     * 图片详情
     */
    public function photo(string $id): Photo
    {
        return Photo::explore()->findOrFail($id);
    }

    /**
     * 举报图片
     */
    public function report(string $id, array $data): Report
    {
        $photo = $this->photo($id);

        /** @var Report $report */
        $report = $photo->reports()->create([...$data, ...[
            'report_user_id' => $photo->user_id,
            'status' => ReportStatus::Unhandled,
        ]]);

        return $report;
    }

    /**
     * 点赞图片
     */
    public function like(string $id): Like
    {
        $photo = $this->photo($id);

        /** @var Like $like */
        $like = $photo->likes()->firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        return $like;
    }

    /**
     * 取消点赞图片
     */
    public function unlike(string $id): bool
    {
        $photo = $this->photo($id);

        return $photo->likes()->where('user_id', Auth::id())->delete() > 0;
    }
}