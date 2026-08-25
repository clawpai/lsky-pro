<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserTicketService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UserTicketService::class;
    }
}
