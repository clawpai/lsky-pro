<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class NoticeService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\NoticeService::class;
    }
}
