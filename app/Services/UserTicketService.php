<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Jobs\SendTicketReplyNotificationMailJob;
use App\Models\Group;
use App\Models\Scopes\FilterScope;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use App\TicketStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserTicketService
{
    /**
     * 创建工单
     * @throws Throwable
     */
    public function store(array $data, string $content): Ticket
    {
        /** @var Ticket $ticket */
        $ticket = DB::transaction(function () use ($data, $content) {
            /** @var User $user */
            $user = Auth::user();

            /** @var Ticket $ticket */
            $ticket = $user->tickets()->create($data);

            $ticket->replies()->create([
                'user_id' => $user->id,
                'content' => $content,
            ]);

            return $ticket;
        });

        // 给所有管理员发送邮件
        /** @var Group $group */
        $group = Context::get('group');
        if ($group->mailDrivers()->exists()) {
            dispatch(new SendTicketReplyNotificationMailJob(
                groupId: $group->id,
                ticket: $ticket->withoutRelations(),
                emails: User::where('is_admin', true)->get()->pluck('email')->toArray(),
            ));
        }

        return $ticket;
    }

    /**
     * 工单回复列表
     */
    public function replies(string $issueNo, ?int $perPage = 40): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Ticket $ticket */
        $ticket = $user->tickets()->with('user')->where('issue_no', $issueNo)->firstOrFail();

        // 设置已读
        $ticket->replies()->where('user_id', '<>', $user->id)->update(['read_at' => now()]);

        return $ticket->replies()->with('user')->oldest()->paginate($perPage);
    }

    /**
     * 工单列表
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        return Auth::user()->tickets()->withGlobalScope('filter', new FilterScope(
            q: data_get($queries, 'q'),
            likes: ['issue_no', 'title'],
            conditions: [
                'sort:level:ascend' => fn(Builder $builder) => $builder->orderBy('level'),
                'sort:level:descend' => fn(Builder $builder) => $builder->orderByDesc('level'),
                'sort:status:ascend' => fn(Builder $builder) => $builder->orderBy('status'),
                'sort:status:descend' => fn(Builder $builder) => $builder->orderByDesc('status'),
                'sort:created_at:ascend' => fn(Builder $builder) => $builder->orderBy('created_at'),
                'sort:created_at:descend' => fn(Builder $builder) => $builder->orderByDesc('created_at'),
            ]
        ))->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 工单详情
     */
    public function show(string $issueNo): Ticket
    {
        /** @var Ticket $ticket */
        $ticket = Auth::user()->tickets()->where('issue_no', $issueNo)->firstOrFail();
        return $ticket;
    }

    /**
     * 回复工单
     * @throws ServiceException
     */
    public function reply(string $issueNo, array $data): TicketReply
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Ticket $ticket */
        $ticket = $user->tickets()->where('issue_no', $issueNo)->firstOrFail();

        if ($ticket->status == TicketStatus::Completed) {
            throw new ServiceException('工单已关闭，无法继续回复');
        }

        /** @var TicketReply $reply */
        $reply = $ticket->replies()->create(array_merge($data, [
            'user_id' => $user->id,
        ]));

        // 给所有管理员发送邮件
        /** @var Group $group */
        $group = Context::get('group');
        if ($group->mailDrivers()->exists()) {
            dispatch(new SendTicketReplyNotificationMailJob(
                groupId: $group->id,
                ticket: $ticket->withoutRelations(),
                emails: User::where('is_admin', true)->get()->pluck('email')->toArray(),
            ));
        }

        return $reply;
    }

    /**
     * 关闭工单
     */
    public function close(string $issueNo): bool
    {
        return Auth::user()->tickets()->where('issue_no', $issueNo)->update(['status' => TicketStatus::Completed]) > 0;
    }

    /**
     * 删除工单
     */
    public function destroy(string $issueNo): bool
    {
        return Auth::user()->tickets()->where('issue_no', $issueNo)->delete() > 0;
    }
}