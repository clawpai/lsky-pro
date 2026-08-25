<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UploadService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UploadService::class;
    }
}
