<?php

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

class R
{
    public static function success(string $message = 'successful', mixed $data = null): Response
    {
        return self::result($message, 'success', $data);
    }

    protected static function result(
        string $message,
        string $status,
        mixed  $data = null,
    ): Response
    {
        $time = now()->timestamp;

        return \response()->json(array_filter(compact('status', 'message', 'data', 'time')));
    }

    public static function error(mixed $message = 'fail', array $data = null): Response
    {
        return self::result($message, 'error', $data);
    }
}