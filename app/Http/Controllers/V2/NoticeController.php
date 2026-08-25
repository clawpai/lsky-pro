<?php

namespace App\Http\Controllers\V2;

use App\Facades\NoticeService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Models\Notice;
use App\Support\R;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class NoticeController extends Controller
{
    /**
     * 公告列表
     */
    public function index(QueryRequest $request): Response
    {
        $notices = NoticeService::paginate($request->validated());

        $notices->getCollection()->each(function (Notice $notice) {
            $notice->content = Str::limit(strip_tags(Str::markdown($notice->content)), 200);
            $notice->setVisible(['id', 'title', 'content', 'created_at']);
        });

        return R::success(data: $notices);
    }

    /**
     * 公告详情
     */
    public function show(string $id)
    {
        $notice = NoticeService::show($id);

        $notice->setVisible(['id', 'title', 'content', 'created_at']);

        return R::success(data: $notice);
    }
}
