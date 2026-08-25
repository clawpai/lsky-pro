<?php

namespace App\Jobs;

use App\Facades\GroupService;
use App\Facades\SmsService;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 发送短信验证码
 */
class SendCodeSmsJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int        $groupId,
        public string     $event,
        public string     $phone,
        public string|int $countryCode = 'cn'
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $group = Group::findOrFail($this->groupId);
        $providers = GroupService::getSmsProviders($group);
        SmsService::instance($providers)->sendCode($this->event, $this->phone, $this->countryCode);
    }

    public function uniqueId(): string
    {
        return md5('send-sms-code:' . $this->event . $this->countryCode . $this->phone);
    }

    public function fail(Throwable $exception = null): void
    {
        Log::error("发送短信验证码执行失败，{$exception?->getMessage()}", [
            'groupId' => $this->groupId,
            'event' => $this->event,
            'photo' => $this->phone,
            'countryCode' => $this->countryCode,
            'trace' => $exception?->getTraceAsString(),
        ]);
    }
}
