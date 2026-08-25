<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class VerifyCodeService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\VerifyCodeService::class;
    }
}
