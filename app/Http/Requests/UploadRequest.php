<?php

namespace App\Http\Requests;

use App\Facades\AppService;
use App\Facades\UserCapacityService;
use App\Models\Group;
use App\Models\User;
use App\Rules\ValidFutureDate;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

class UploadRequest extends FormRequest
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
        /** @var Group $group */
        $group = Context::get('group');

        /** @var User $user */
        $user = Auth::guard('sanctum')->user();

        // 支持的文件类型
        $allowFileTypes = collect(AppService::getAllSupportedImageTypes())
            ->intersect($group->options['allow_file_types'])
            ->implode(',');

        return [
            'file' => [
                'required',
                'file',
                'mimes:' . $allowFileTypes,
                'max:' . $group->options['max_upload_size'],
                function (string $attribute, UploadedFile $value, Closure $fail) use ($user) {
                    // 验证储存空间是否充足
                    if (!is_null($user)) {
                        $total = UserCapacityService::getUserTotalCapacity();
                        $size = $value->getSize() / 1024 + $user->photos()->sum('size');
                        if ($size > $total) {
                            $fail('储存空间不足');
                        }
                    }
                },
            ],
            'storage_id' => ['required', 'integer'],
            'album_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    if (!is_null($user) && !$user->albums()->where('id', $value)->exists()) {
                        $fail('相册不存在');
                    }
                }
            ],
            'expired_at' => ['nullable', 'date', 'after:now', new ValidFutureDate()],
            'tags' => ['nullable', 'array', 'max:10'],
            'is_public' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => '文件',
            'storage_id' => '储存',
            'album_id' => '相册',
            'expired_at' => '到期时间',
            'tags' => '标签',
            'is_public' => '是否公开',
        ];
    }
}
