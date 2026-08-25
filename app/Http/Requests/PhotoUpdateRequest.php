<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PhotoUpdateRequest extends FormRequest
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
            'ids' => ['required', 'array'],
            'ids.*' => ['required'],
            'name' => ['nullable', 'string', 'max:200'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'is_public' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ids' => '图片ID',
            'ids.*' => '图片ID',
            'name' => '图片名称',
            'intro' => '图片简介',
            'is_public' => '是否公开',
            'tags' => '标签信息',
        ];
    }
}
