<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class TicketService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\TicketService::class;
    }
}
