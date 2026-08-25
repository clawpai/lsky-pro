<?php

namespace App\Jobs;

use App\Facades\GroupService;
use App\Facades\MailService;
use App\Jobs\Middleware\RateLimited;
use App\Models\Group;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 发送工单回复通知邮件
 */
class SendTicketReplyNotificationMailJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Queueable;

    public int $groupId;

    public Ticket $ticket;

    public array $emails;

    /**
     * Create a new job instance.
     */
    public function __construct(int $groupId, Ticket $ticket, array $emails)
    {
        $this->groupId = $groupId;
        $this->ticket = $ticket->withoutRelations();
        $this->emails = $emails;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $group = Group::findOrFail($this->groupId);
        $providers = GroupService::getMailProviders($group);
        MailService::instance($providers)->sendTicketReplyNotification($this->ticket, $this->emails);
    }

    public function uniqueId(): string
    {
        return md5('send-mail-ticket-reply-notification:' . implode(',', $this->emails));
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited];
    }

    public function fail(Throwable $exception = null): void
    {
        Log::error("发送工单回复通知邮件，{$exception?->getMessage()}", [
            'groupId' => $this->groupId,
            'ticket' => $this->ticket,
            'emails' => $this->emails,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
