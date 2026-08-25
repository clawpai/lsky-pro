<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class PhotoHandleService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\PhotoHandleService::class;
    }
}
