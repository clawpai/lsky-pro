<?php

namespace App\Http\Requests;

use App\Facades\AppService;
use App\Models\User;
use App\Rules\ValidVerifyCode;
use App\VerifyCodeEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;

class BindPhoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                (new Phone())->countryField('country_code'),
                Rule::unique(User::class)->ignore(Auth::id())
            ],
            'country_code' => [
                'required',
                'string',
                Rule::in(array_keys(AppService::getAllCountryCodes())),
            ],
            'code' => [
                'required',
                new ValidVerifyCode(VerifyCodeEvent::Bind->value, $this->post('phone')),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'country_code' => '国家代码',
            'phone' => '手机号',
            'code' => '验证码',
        ];
    }
}
