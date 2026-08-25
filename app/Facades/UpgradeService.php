<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class UpgradeService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\UpgradeService::class;
    }
}
