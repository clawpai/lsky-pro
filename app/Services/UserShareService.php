<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\Scopes\FilterScope;
use App\Models\Share;
use App\Models\User;
use App\ShareType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class UserShareService
{
    /**
     * 获取用户分享分页列表
     *
     * @param array $queries
     * @return LengthAwarePaginator
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        return Auth::user()->shares()
            ->withGlobalScope('filter', new FilterScope(
                q: data_get($queries, 'q'),
                likes: ['slug', 'content'],
                conditions: [
                    'sort:view_count:ascend' => fn(Builder $builder) => $builder->orderBy('view_count'),
                    'sort:view_count:descend' => fn(Builder $builder) => $builder->orderByDesc('view_count'),
                    'sort:expired_at:ascend' => fn(Builder $builder) => $builder->orderBy('expired_at'),
                    'sort:expired_at:descend' => fn(Builder $builder) => $builder->orderByDesc('expired_at'),
                    'sort:created_at:ascend' => fn(Builder $builder) => $builder->orderBy('created_at'),
                    'sort:created_at:descend' => fn(Builder $builder) => $builder->orderByDesc('created_at'),
                ]
            ))
            ->withCount('likes as like_count')
            ->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 创建分享
     *
     * @param array $data 分享信息
     * @param array $ids 相册或图片ID
     * @return Share
     * @throws Throwable
     */
    public function store(array $data, array $ids): Share
    {
        return DB::transaction(function () use ($data, $ids) {
            /** @var User $user */
            $user = Auth::user();

            /** @var Share $share */
            $share = $user->shares()->create(array_merge($data, ['slug' => str_replace('-', '', Str::uuid()->toString())]));

            if ($share->type == ShareType::Album) {
                $ids = $user->albums()->whereIn('id', $ids)->pluck('id')->toArray();

                if (count($ids) <= 0) {
                    throw new ServiceException('没有找到相册');
                }

                $share->albums()->syncWithoutDetaching($ids);
            } else {
                $ids = $user->photos()->whereIn('id', $ids)->pluck('id')->toArray();

                if (count($ids) <= 0) {
                    throw new ServiceException('没有找到图片');
                }

                $share->photos()->syncWithoutDetaching($ids);
            }

            return $share;
        });
    }

    /**
     * 分享详情
     */
    public function show(string $id): Share
    {
        /** @var Share $share */
        $share = Auth::user()->shares()
            ->withCount('likes as like_count')
            ->findOrFail($id);

        return $share;
    }

    /**
     * 修改分享
     *
     * @param string $id
     * @param array $data
     * @return bool
     * @throws Throwable
     */
    public function update(string $id, array $data): bool
    {
        /** @var Share $share */
        $share = Auth::user()->shares()->findOrFail($id);

        $share->fill($data);

        return $share->save();
    }

    /**
     * 删除分享
     */
    public function destroy(array $ids): bool
    {
        return Auth::user()->shares()->whereIn('id', $ids)->delete() > 0;
    }
}