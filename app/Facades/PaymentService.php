<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\PaymentService::class;
    }
}
