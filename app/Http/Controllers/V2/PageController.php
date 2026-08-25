<?php

namespace App\Http\Controllers\V2;

use App\Facades\PageService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Models\Page;
use App\Support\R;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    /**
     * 页面列表
     */
    public function index(QueryRequest $request): Response
    {
        $notices = PageService::paginate($request->validated());

        $notices->getCollection()->each(function (Page $notice) {
            $notice->setVisible(['id', 'type', 'icon', 'name', 'title', 'slug', 'url', 'view_count']);
        });

        return R::success(data: $notices);
    }

    /**
     * 页面详情
     */
    public function show(string $slug)
    {
        $notice = PageService::show($slug);

        $notice->setVisible(['id', 'type', 'icon', 'name', 'title', 'slug', 'url', 'view_count', 'content', 'created_at']);

        return R::success(data: $notice);
    }
}
