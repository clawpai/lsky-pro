<?php

namespace App\Http\Responses\Auth;

use App\Support\R;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): Response
    {
        return R::success()->setStatusCode(201);
    }
}
