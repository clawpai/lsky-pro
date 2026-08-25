<?php

namespace App\Http\Responses\Auth;

use App\Support\R;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): Response
    {
        return R::success()->setStatusCode(204);
    }
}
