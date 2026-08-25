<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserUpdateProfileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => ['nullable', 'file', 'mimes:jpg,gif,png', 'max:2048'],
            'username' => ['nullable', 'max:20', Rule::unique('users')->ignoreModel(Auth::user())],
            'name' => ['nullable', 'max:20'],
            'tagline' => ['nullable', 'max:60'],
            'bio' => ['nullable', 'max:200'],
            'url' => ['nullable', 'url', 'max:200'],
            'company' => ['nullable', 'max:80'],
            'company_title' => ['nullable', 'max:60'],
            'location' => ['nullable', 'max:60'],
            'interests' => ['nullable', 'array', 'max:6'],
            'socials' => ['nullable', 'array', 'max:6'],
        ];
    }

    public function attributes(): array
    {
        return [
            'avatar' => '头像',
            'username' => '用户名',
            'name' => '昵称',
            'tagline' => '个性签名',
            'bio' => '个人简介',
            'url' => '个人网站',
            'company' => '公司',
            'company_title' => '职位',
            'location' => '位置',
            'interests' => '兴趣爱好',
            'socials' => '社交账号',
        ];
    }
}
