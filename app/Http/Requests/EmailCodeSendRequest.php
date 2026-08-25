<?php

namespace App\Http\Requests;

use App\VerifyCodeEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EmailCodeSendRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 部分事件需要登录后操作
        if (in_array($this->post('event'), [VerifyCodeEvent::Bind->value, VerifyCodeEvent::Verify->value])) {
            return Auth::guard('sanctum')->check();
        }

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
            'event' => ['required', Rule::enum(VerifyCodeEvent::class)],
            'email' => ['required', 'email'],
            'captcha' => 'required|captcha_api:'. request('captcha_key') . ',math'
        ];
    }

    public function attributes(): array
    {
        return [
            'captcha' => '图形验证码',
        ];
    }
}
