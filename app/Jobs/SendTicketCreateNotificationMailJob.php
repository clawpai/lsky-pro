<?php

namespace App\Jobs;

use App\Facades\GroupService;
use App\Facades\MailService;
use App\Models\Group;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 给管理员发送工单创建通知邮件
 */
class SendTicketCreateNotificationMailJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
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
        MailService::instance($providers)->sendTicketCreateNotification($this->ticket, $this->emails);
    }

    public function uniqueId(): string
    {
        return md5('send-mail-ticket-create-notification:' . $this->ticket->issue_no);
    }

    public function fail(Throwable $exception = null): void
    {
        Log::error("给管理员发送工单创建通知邮件，{$exception?->getMessage()}", [
            'groupId' => $this->groupId,
            'ticket' => $this->ticket,
            'emails' => $this->emails,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
