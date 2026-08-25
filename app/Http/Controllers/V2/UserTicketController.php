<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserTicketService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Http\Requests\TicketReplyRequest;
use App\Http\Requests\TicketStoreRequest;
use App\Http\Resources\UserTicketResource;
use App\Models\TicketReply;
use App\Support\R;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class UserTicketController extends Controller
{
    /**
     * 工单列表
     */
    public function index(QueryRequest $request): Response
    {
        $tickets = UserTicketService::paginate($request->validated());

        return R::success(data: UserTicketResource::collection($tickets)->response()->getData());
    }

    /**
     * 创建工单
     */
    public function store(TicketStoreRequest $request): Response
    {
        $ticket = UserTicketService::store(Arr::only($request->validated(), ['title', 'level']), $request->validated('content'));

        return R::success(data: $ticket->only(['issue_no']))->setStatusCode(201);
    }

    /**
     * 工单详情
     */
    public function show(string $issueNo): Response
    {
        $ticket = UserTicketService::show($issueNo);

        return R::success(data: UserTicketResource::make($ticket));
    }

    /**
     * 工单回复列表
     */
    public function replies(string $issueNo, Request $request): Response
    {
        $replies = UserTicketService::replies($issueNo, (int)$request->query('per_page'));

        $replies->getCollection()->each(function (TicketReply $reply) {
            $reply->user->append('avatar_url')->setVisible(['id', 'name', 'avatar_url']);
            $reply->setVisible(['id', 'user', 'content', 'read_at', 'created_at']);
        });

        return R::success(data: $replies);
    }

    /**
     * 回复工单
     */
    public function reply(string $issueNo, TicketReplyRequest $request): Response
    {
        UserTicketService::reply($issueNo, $request->validated());

        return R::success()->setStatusCode(201);
    }

    /**
     * 关闭工单
     */
    public function close(string $issueNo): Response
    {
        UserTicketService::close($issueNo);

        return R::success()->setStatusCode(204);
    }

    /**
     * 删除工单
     */
    public function destroy(string $issueNo): Response
    {
        UserTicketService::destroy($issueNo);

        return R::success()->setStatusCode(204);
    }
}
