<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserGroupService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UserGroupService::class;
    }
}
