<?php

namespace App\Http\Responses\Auth;

use App\Support\R;
use Laravel\Fortify\Contracts\PasswordUpdateResponse as PasswordUpdateResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasswordUpdateResponse implements PasswordUpdateResponseContract
{
    public function toResponse($request): Response
    {
        return R::success();
    }
}
