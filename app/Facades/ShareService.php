<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ShareService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\ShareService::class;
    }
}
