<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageService
{
    /**
     * 页面列表
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        return Page::where('is_show', true)->orderByDesc('sort')->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 页面详情
     */
    public function show(string $slug): Page
    {
        /** @var Page $page */
        $page = Page::where('is_show', true)->where('slug', $slug)->firstOrFail();

        $page->increment('view_count');

        return $page;
    }
}