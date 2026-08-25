<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ExploreAlbumService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\ExploreAlbumService::class;
    }
}
