<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class ReportService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\ReportService::class;
    }
}
