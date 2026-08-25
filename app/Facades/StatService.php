<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class StatService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\StatService::class;
    }
}
