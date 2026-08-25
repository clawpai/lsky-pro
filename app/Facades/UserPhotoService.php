<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserPhotoService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UserPhotoService::class;
    }
}
