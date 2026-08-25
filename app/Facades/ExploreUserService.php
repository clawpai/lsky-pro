<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ExploreUserService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\ExploreUserService::class;
    }
}
