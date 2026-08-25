<?php

namespace App\Http\Controllers\V1;

use App\Facades\AppService;
use App\Facades\UploadService;
use App\Facades\UserCapacityService;
use App\Models\Group;
use App\Models\Photo;
use App\Models\User;
use App\Rules\ValidFutureDate;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ImageController extends BaseController
{
    public function upload(Request $request): Response
    {
        /** @var Group $group */
        $group = Context::get('group');

        /** @var User|null $user */
        $user = Auth::guard('sanctum')->user();

        // 支持的文件类型
        $allowFileTypes = collect(AppService::getAllSupportedImageTypes())
            ->intersect($group->options['allow_file_types'])
            ->implode(',');

        $rules = [
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
            'token' => 'nullable',
            'permission' => 'in:1,0',
            'strategy_id' => ['nullable', 'numeric'],
            'album_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    if (!is_null($user) && !$user->albums()->where('id', $value)->exists()) {
                        $fail('相册不存在');
                    }
                }
            ],
            'expired_seconds' => 'nullable|numeric|max:15759360',
            'expired_at' => ['nullable', 'date', 'after:now', new ValidFutureDate()],
        ];

        $validator = Validator::make($request->only(array_keys($rules)), $rules, [
            'permission.in' => '权限值设置错误',
            'expired_seconds.numeric' => '自动删除时间设置错误',
            'expired_seconds.max' => '自动删除时间最大 6 个月',
            'expired_at.date' => '自定义删除时间设置错误',
        ]);

        try {
            $validated = collect($validator->validated());
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first());
        }

        $token = $validated->get('token');
        if (is_null($user) && $token) {
            if (!Cache::has($token)) {
                return $this->fail('Token 无效或已过期');
            }
            $user = User::query()->find(Cache::get($token));
        }

        try {
            $merge = [
                'storage_id' => $validated->get('strategy_id'),
                'album_id' => $validated->get('album_id'),
                'expired_at' => $validated->get('expired_at'),
                'is_public' => (int)$validated->get('permission'),
            ];

            $image = UploadService::uploadImage($request->file('file'), $user, $merge);
        } catch (UploadException $e) {
            return $this->fail($e->getMessage());
        } catch (Throwable $e) {
            if (config('app.debug')) {
                return $this->fail($e->getMessage());
            }
            return $this->fail('服务异常，请稍后再试');
        }

        $image = $this->getImageRow($image);
        return $this->success('上传成功', array_merge($image->only(
            'key', 'name', 'pathname', 'size', 'mimetype', 'extension', 'md5', 'sha1', 'links'
        ), ['origin_name' => $image->filename]));
    }

    protected function getImageRow(Photo $image): Photo
    {
        $image->key = $image->id;
        $image->human_date = $image->created_at->diffForHumans();
        $image->date = $image->created_at->format('Y-m-d H:i:s');

        $image->links = [
            'url' => $image->public_url,
            'html' => "&lt;img src=\"{$image->public_url}\" alt=\"{$image->filename}\" title=\"{$image->filename}\" /&gt;",
            'bbcode' => "[img]{$image->public_url}[/img]",
            'markdown' => "![{$image->filename}]({$image->public_url})",
            'markdown_with_link' => "[![{$image->filename}]({$image->public_url})]({$image->public_url})",
            'thumbnail_url' => $image->thumbnail_url,
            'delete_url' => '',
        ];

        $image->setVisible([
            'album', 'key', 'name', 'pathname', 'origin_name', 'size', 'mimetype', 'extension', 'md5', 'sha1',
            'width', 'height', 'links', 'human_date', 'date',
        ]);

        return $image;
    }

    public function tokens(Request $request): Response
    {
        try {
            $tokens = $this->genTempUploadTokens($request);
        } catch (UploadException $e) {
            return $this->fail($e->getMessage());
        } catch (Throwable $e) {
            if (config('app.debug')) {
                return $this->fail($e->getMessage());
            }
            return $this->fail('服务异常，请稍后再试');
        }
        return $this->success('生成成功', compact('tokens'));
    }

    /**
     * 生成临时图片 token
     *
     * @param Request $request
     * @return array
     * @throws UploadException
     */
    public function genTempUploadTokens(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();

        $rules = [
            'num' => 'required|numeric|min:1|max:100',
            'seconds' => 'required|numeric|max:2626560',
        ];

        $validator = Validator::make($request->only(array_keys($rules)), $rules, [
            'num.numeric' => '数量必须是数字',
            'num.min' => '最小数量为 1',
            'num.max' => '最大数量为 100',
            'seconds.numeric' => '自动删除时间设置错误',
            'seconds.max' => '到期时间时间最大 1 个月',
        ]);

        try {
            $validated = collect($validator->validated());
        } catch (ValidationException $e) {
            throw new UploadException($e->validator->errors()->first());
        }

        $array = [];
        for ($i = 0; $i < $validated['num']; $i++) {
            $key = md5(Str::random(32) . $user->id);
            Cache::put($key, $user->id, (int)$validated['seconds']);
            $array[] = [
                'token' => $key,
                'expired_at' => Carbon::now()->addSeconds((int)$validated['seconds'])->format('Y-m-d H:i:s'),
            ];
        }

        return $array;
    }

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $images = $user->photos()->with('storage')->where(function (Builder $builder) use ($request) {
            $builder->when($request->query('order') ?: 'newest', function (Builder $builder, $order) {
                match ($order) {
                    'earliest' => $builder->orderBy('id'),
                    'utmost' => $builder->orderByDesc('size'),
                    'least' => $builder->orderBy('size'),
                    default => $builder->orderByDesc('id'),
                };
            })->when($request->query('permission') ?: 'all', function (Builder $builder, $permission) {
                switch ($permission) {
                    case 'public':
                        $builder->where('is_public', 1);
                        break;
                    case 'private':
                        $builder->where('is_public', 0);
                        break;
                }
            })->when($request->query('q'), function (Builder $builder, $q) {
                $builder->where('name', 'like', "%{$q}%")->orWhere('filename', 'like', "%{$q}%");
            })->when((int)$request->query('album_id'), function (Builder $builder, $albumId) {
                $builder->where('album_id', $albumId);
            });
        })->paginate(40)->withQueryString();
        $images->getCollection()->each(function (Photo $image) {
            return $this->getImageRow($image);
        });
        return $this->success('success', $images);
    }

    public function destroy(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $user->photos()->where('id', $request->input('key'))->delete();
        return $this->success('删除成功');
    }
}
