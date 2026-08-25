<?php

namespace App\Jobs\Middleware;

use Closure;
use Illuminate\Contracts\Redis\LimiterTimeoutException;
use Illuminate\Support\Facades\Redis;

/**
 * 限制每 60 秒处理一个任务
 */
class RateLimited
{
    /**
     * Process the queued job.
     *
     * @param Closure(object): void $next
     * @throws LimiterTimeoutException
     */
    public function handle(object $job, Closure $next): void
    {
        Redis::throttle('key')
            ->block(0)->allow(1)->every(60)
            ->then(function () use ($job, $next) {
                $next($job);
            }, function () use ($job) {
                $job->release(5);
            });
    }
}