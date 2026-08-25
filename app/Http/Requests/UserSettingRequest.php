<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserSettingRequest extends FormRequest
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
            'language' => ['nullable', 'string', 'max:20'],
            'show_original_photos' => ['nullable', 'boolean'],
            'encode_copied_url' => ['nullable', 'boolean'],
            'auto_upload_after_select' => ['nullable', 'boolean'],
            'upload_button_action' => ['nullable', 'string'],
            'default_storage_id' => ['nullable', 'integer', Rule::exists('storages', 'id')->withoutTrashed()],
        ];
    }
}
