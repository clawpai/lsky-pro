<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use App\Facades\AppService;
use App\Models\User;
use App\Rules\ValidVerifyCode;
use App\VerifyCodeEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;

class SmsResetPasswordRequest extends FormRequest
{
    use PasswordValidationRules;

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
                Rule::exists(User::class),
            ],
            'country_code' => [
                'required',
                'string',
                Rule::in(array_keys(AppService::getAllCountryCodes()))
            ],
            'password' => $this->passwordRules(),
            'code' => [
                'required',
                new ValidVerifyCode(VerifyCodeEvent::ForgetPassword->value, $this->post('phone', '')),
            ],
        ];
    }
}
