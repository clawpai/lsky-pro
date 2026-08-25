<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserCapacityService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UserCapacityService::class;
    }
}
