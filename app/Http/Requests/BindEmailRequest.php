<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\ValidVerifyCode;
use App\VerifyCodeEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BindEmailRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                Rule::unique(User::class)->ignore(Auth::id())
            ],
            'code' => [
                'required',
                new ValidVerifyCode(VerifyCodeEvent::Bind->value, $this->post('email', '')),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => '邮箱',
            'code' => '验证码',
        ];
    }
}
