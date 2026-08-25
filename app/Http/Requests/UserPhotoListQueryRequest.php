<?php

namespace App\Http\Requests;

class UserPhotoListQueryRequest extends QueryRequest
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
        return array_merge(parent::rules(), [
            'album_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
            'storage_id' => 'nullable|integer',
        ]);
    }
}
