<?php

namespace App\Jobs;

use App\Facades\GroupService;
use App\Facades\MailService;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 发送邮件验证码
 */
class SendCodeMailJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int    $groupId,
        public string $event,
        public string $email,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $group = Group::findOrFail($this->groupId);
        $providers = GroupService::getMailProviders($group);
        MailService::instance($providers)->sendCode($this->event, $this->email);
    }

    public function uniqueId(): string
    {
        return md5('send-mail-code:' . $this->event . $this->email);
    }

    public function fail(Throwable $exception = null): void
    {
        Log::error("发送邮件验证码执行失败，{$exception?->getMessage()}", [
            'groupId' => $this->groupId,
            'event' => $this->event,
            'email' => $this->email,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
