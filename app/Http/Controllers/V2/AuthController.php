<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\MailResetPasswordRequest;
use App\Http\Requests\SmsResetPasswordRequest;
use App\Support\R;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * 使用邮箱重置密码
     */
    public function mailResetPassword(MailResetPasswordRequest $request): Response
    {
        UserService::resetPassword($request->user(), $request->validated('password'));
        return R::success()->setStatusCode(201);
    }

    /**
     * 使用手机号重置密码
     */
    public function smsResetPassword(SmsResetPasswordRequest $request): Response
    {
        UserService::resetPassword($request->user(), $request->validated('password'));
        return R::success()->setStatusCode(201);
    }
}
