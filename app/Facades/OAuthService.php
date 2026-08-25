<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class OAuthService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\OAuthService::class;
    }
}
