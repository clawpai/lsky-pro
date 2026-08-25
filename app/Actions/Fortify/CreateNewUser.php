<?php

namespace App\Actions\Fortify;

use App\Exceptions\ServiceException;
use App\Facades\AppService;
use App\Facades\AuthService;
use App\Facades\UserService;
use App\Models\User;
use App\Rules\ValidVerifyCode;
use App\Settings\AppSettings;
use App\VerifyCodeEvent;
use Arr;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Propaganistas\LaravelPhone\Rules\Phone;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array<string, string> $input
     */
    public function create(array $input): User
    {
        $input = array_merge([
            'email' => null,
            'phone' => null,
        ], $input);

        $appSettings = app(AppSettings::class);

        // 检测是否支持游客上传
        if (!$appSettings->enable_registration) {
            throw new ServiceException('系统已关闭注册功能');
        }

        $rules = [
            'email' => [
                'nullable',
                'string',
                'max:255',
                'email',
                Rule::requiredIf(empty($input['phone'])),
                Rule::unique(User::class),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:255',
                (new Phone())->countryField('country_code'),
                Rule::requiredIf(empty($input['email'])),
                Rule::unique(User::class),
                function (string $attribute, mixed $value, Closure $fail) use ($appSettings) {
                    if (! $appSettings->user_phone_verify) {
                        $fail('系统暂不支持手机号注册');
                    }
                }
            ],
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'alpha_dash',
                'max:200',
                Rule::unique(User::class),
            ],
            'country_code' => [
                'nullable',
                'string',
                Rule::requiredIf(!empty($input['phone'])),
                Rule::in(array_keys(AppService::getAllCountryCodes())),
            ],
            'password' => $this->passwordRules(),
        ];

        // 是否需要验证
        // 优先判断是否有手机号，有手机号则表示是手机号注册
        $from = $input['phone'] ? 'mobile' : 'email';

        if ($appSettings->user_phone_verify && $from === 'mobile') {
            $rules['code'] = [
                'required',
                new ValidVerifyCode(VerifyCodeEvent::Register->value, $input['phone']),
            ];
        }

        if ($appSettings->user_email_verify && $from === 'email') {
            $rules['code'] = [
                'required',
                new ValidVerifyCode(VerifyCodeEvent::Register->value, $input['email']),
            ];
        }

        Validator::make($input, $rules)->validate();

        if ($input['email']) {
            $input['email_verified_at'] = now();
        } else if ($input['phone']) {
            $input['phone_verified_at'] = now();
        }

        $user = UserService::store([
            ...Arr::only(array_filter($input), [
                'username', 'name', 'email', 'phone', 'country_code', 'email_verified_at', 'phone_verified_at',
            ]),
            'password' => Hash::make($input['password']),
            'register_ip' => AppService::getRequestIp(request()),
        ]);

        if (array_key_exists('token', $input) && $input['token']) {
            $content = AuthService::getOAuthLoginVerifyTokenContent($input['token']);
            if (!is_null($content)) {
                $user->oauth()->firstOrCreate($content);
            }
        }

        return $user;
    }
}
