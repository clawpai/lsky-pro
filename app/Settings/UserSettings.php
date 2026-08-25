<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class UserSettings extends Settings
{
    /** @var null|float 用户初始容量 */
    public ?float $initial_capacity = null;

    public static function group(): string
    {
        return 'user';
    }
}