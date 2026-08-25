<?php

namespace App\Http\Requests;

use App\TicketLevel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketStoreRequest extends FormRequest
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
            'title' => ['required', 'max:200'],
            'level' => ['required', Rule::enum(TicketLevel::class)],
            'content' => ['required', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => '标题',
            'level' => '级别',
            'content' => '内容',
        ];
    }
}
