<?php

namespace App\Http\Requests;

use App\Rules\ValidFutureDate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShareUpdateRequest extends FormRequest
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
            'content' => ['nullable', 'string', 'max:2000'],
            'password' => ['nullable', 'string', 'max:200'],
            'expired_at' => ['nullable', 'date', 'after:now', new ValidFutureDate()],
        ];
    }

    public function attributes(): array
    {
        return [
            'content' => '内容',
            'password' => '密码',
            'expired_at' => '到期时间',
        ];
    }
}
