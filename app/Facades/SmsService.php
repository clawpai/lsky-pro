<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SmsService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\SmsService::class;
    }
}
