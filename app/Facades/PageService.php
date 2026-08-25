<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class PageService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\PageService::class;
    }
}
