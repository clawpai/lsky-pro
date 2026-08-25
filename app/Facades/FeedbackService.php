<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class FeedbackService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\FeedbackService::class;
    }
}
