<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class MailService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\MailService::class;
    }
}
