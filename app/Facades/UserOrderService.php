<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserOrderService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UserOrderService::class;
    }
}
