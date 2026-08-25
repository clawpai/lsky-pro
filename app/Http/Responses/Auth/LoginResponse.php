<?php

namespace App\Http\Responses\Auth;

use App\Facades\AuthService;
use App\Support\R;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        return R::success(data: AuthService::getLoginResult());
    }
}