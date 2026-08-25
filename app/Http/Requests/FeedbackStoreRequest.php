<?php

namespace App\Http\Requests;

use App\FeedbackType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedbackStoreRequest extends FormRequest
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
            'type' => ['required', Rule::enum(FeedbackType::class)],
            'title' => ['required', 'max:200'],
            'name' => ['required', 'max:80'],
            'email' => ['required', 'email', 'max:100'],
            'content' => ['required', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => '类型',
            'title' => '标题',
            'name' => '姓名',
            'email' => '邮箱',
            'content' => '反馈内容',
        ];
    }
}
