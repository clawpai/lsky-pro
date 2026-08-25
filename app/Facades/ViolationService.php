<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ViolationService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\ViolationService::class;
    }
}
