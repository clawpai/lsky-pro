<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ExplorePhotoService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\ExplorePhotoService::class;
    }
}
