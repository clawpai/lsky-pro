<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserShareService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UserShareService::class;
    }
}
