<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UserAlbumService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UserAlbumService::class;
    }
}
