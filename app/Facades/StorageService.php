<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class StorageService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\StorageService::class;
    }
}
