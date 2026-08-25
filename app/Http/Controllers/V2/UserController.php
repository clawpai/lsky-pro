<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\BindEmailRequest;
use App\Http\Requests\BindPhoneRequest;
use App\Http\Requests\UserSettingRequest;
use App\Http\Requests\UserUpdateProfileRequest;
use App\Support\R;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * 登录用户信息
     */
    public function profile(): Response
    {
        return R::success(data: UserService::getProfile());
    }

    /**
     * 修改登录用户信息
     */
    public function updateProfile(UserUpdateProfileRequest $request): Response
    {
        $data = Arr::map(array_filter($request->validated(), fn ($item) => ! is_null($item)), function ($item) {
            return is_array($item) ? array_filter($item) : $item;
        });

        if (! UserService::updateProfile($data)) {
            return R::error();
        }

        return R::success();
    }

    /**
     * 用户设置
     */
    public function updateSetting(UserSettingRequest $request): Response
    {
        if (! UserService::updateSetting($request->validated())) {
            return R::error();
        }

        return R::success();
    }

    /**
     * 绑定手机号
     */
    public function bindPhone(BindPhoneRequest $request): Response
    {
        UserService::bindPhone($request->validated('phone'), $request->validated('country_code', 'cn'));
        return R::success()->setStatusCode(201);
    }

    /**
     * 绑定邮箱
     */
    public function bindEmail(BindEmailRequest $request): Response
    {
        UserService::bindEmail($request->validated('email'));
        return R::success()->setStatusCode(201);
    }
}
