<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notice;
use App\Models\Scopes\FilterScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoticeService
{
    /**
     * 公告列表
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        return Notice::withGlobalScope('filter', new FilterScope(
            q: data_get($queries, 'q'),
            likes: ['title', 'content'],
        ))->orderByDesc('sort')->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 公告详情
     */
    public function show(string $id): Notice
    {
        /** @var Notice $notice */
        $notice = Notice::findOrFail($id);

        $notice->increment('view_count');

        return $notice;
    }
}