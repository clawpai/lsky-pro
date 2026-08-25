<?php

namespace App\Http\Requests;

use App\ApiPermission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TokenStoreRequest extends FormRequest
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
            'name' => ['required', 'max:40'],
            'expires_at' => ['nullable', 'date'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'in:' . implode(',', array_map(fn($p) => $p->value, ApiPermission::cases()))],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '名称',
            'expires_at' => '到期时间',
            'abilities' => '权限',
            'abilities.*' => '权限项',
        ];
    }
}
