<?php

namespace App\Http\Requests;

use App\Rules\ValidFutureDate;
use App\ShareType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareStoreRequest extends FormRequest
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
            'type' => ['required', Rule::enum(ShareType::class)],
            'ids' => ['required', 'array', Rule::when($this->post('type') === ShareType::Album->value, 'max:1')],
        ];
    }

    public function attributes(): array
    {
        return [
            'content' => '内容',
            'password' => '密码',
            'expired_at' => '到期时间',
            'type' => '类型',
            'ids' => '分享内容',
        ];
    }
}
