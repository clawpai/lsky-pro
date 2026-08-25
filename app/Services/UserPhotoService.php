<?php

declare(strict_types=1);

namespace App\Services;

use App\Facades\PhotoService;
use App\Models\Photo;
use App\Models\Scopes\FilterScope;
use App\Models\Tag;
use App\Models\User;
use App\PhotoStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserPhotoService
{
    protected function getQuery(?string $q = ''): HasMany
    {
        return Auth::user()->photos()->withGlobalScope('filter', new FilterScope(
            q: $q,
            likes: ['name', 'intro', 'filename', 'pathname']
        ));
    }

    /**
     * 图片列表
     */
    public function paginate($queries = []): LengthAwarePaginator
    {
        $albumId = data_get($queries, 'album_id');
        $groupId = data_get($queries, 'group_id');
        $storageId = data_get($queries, 'storage_id');

        return $this->getQuery(data_get($queries, 'q'))
            ->with('group', 'storage', 'albums', 'tags')
            ->when($groupId, fn(Builder $builder) => $builder->where('group_id', $groupId))
            ->when($storageId, fn(Builder $builder) => $builder->where('storage_id', $storageId))
            ->when($albumId, function (Builder $builder) use ($albumId) {
                $builder->whereHas('albums', function (Builder $builder) use ($albumId) {
                    $builder->where('id', $albumId);
                });
            })
            ->where('status', PhotoStatus::Normal)
            ->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 图片详情
     */
    public function show(string $id): Photo
    {
        /** @var Photo $photo */
        $photo = $this->getQuery()->with('tags')->findOrFail($id);

        return $photo;
    }

    /**
     * 修改图片信息
     */
    public function update(string $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            /** @var Photo $photo */
            $photo = Auth::user()->photos()->findOrFail($id);

            if (isset($data['tags'])) {
                $attach = [];
                foreach ($data['tags'] as $name) {
                    $attach[Tag::firstOrCreate(compact('name'))->id] = ['user_id' => $photo->user_id];
                }
                $photo->tags()->sync($attach);
                unset($data['tags']);
            }

            $photo->fill($data);

            return $photo->save();
        });
    }

    /**
     * 删除图片
     */
    public function destroy(array $ids): int
    {
        /** @var User $user */
        $user = Auth::user();

        // 只获取属于当前用户的图片ID
        $userPhotoIds = $user->photos()->whereIn('id', $ids)->pluck('id')->toArray();

        if (empty($userPhotoIds)) {
            return 0;
        }

        return PhotoService::destroy($userPhotoIds);
    }

    /**
     * 附加标签
     *
     * @param string $id 图片ID
     * @param array $tags 标签
     * @return int
     * @throws Throwable
     */
    public function attachTags(string $id, array $tags): int
    {
        /** @var Photo $photo */
        $photo = $this->getQuery()->findOrFail($id);

        $attach = DB::transaction(function () use ($photo, $tags) {
            $attach = [];
            foreach ($tags as $name) {
                $attach[Tag::firstOrCreate(compact('name'))->id] = ['user_id' => $photo->user_id];
            }

            $photo->tags()->sync($attach);

            return $attach;
        });

        return count($attach);
    }

    /**
     * 移除标签
     *
     * @param string $id 图片ID
     * @param array $tags 标签
     * @return int
     */
    public function removeTags(string $id, array $tags): int
    {
        /** @var Photo $photo */
        $photo = Auth::user()->photos()->findOrFail($id);

        $ids = Tag::whereIn('name', $tags)->pluck('id')->toArray();

        $photo->tags()->detach($ids);

        return count($ids);
    }

    /**
     * 批量修改图片信息
     */
    public function batchUpdate(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            $ids = data_get($data, 'ids', []);
            $photos = Auth::user()->photos()->whereIn('id', $ids)->get();
            
            if ($photos->isEmpty()) {
                return false;
            }

            $updateFields = [];
            
            // 只更新传入的字段
            if (array_key_exists('name', $data)) {
                $updateFields['name'] = $data['name'];
            }
            
            if (array_key_exists('intro', $data)) {
                $updateFields['intro'] = $data['intro'];
            }
            
            if (array_key_exists('is_public', $data)) {
                $updateFields['is_public'] = $data['is_public'];
            }
            
            // 只有当有字段需要更新时才进行更新
            if (!empty($updateFields)) {
                foreach ($photos as $photo) {
                    $photo->fill($updateFields);
                    $photo->save();
                }
            }
            
            // 处理标签
            if (array_key_exists('tags', $data) && is_array($data['tags'])) {
                foreach ($photos as $photo) {
                    $attach = [];
                    foreach ($data['tags'] as $name) {
                        $attach[Tag::firstOrCreate(compact('name'))->id] = ['user_id' => $photo->user_id];
                    }
                    $photo->tags()->sync($attach);
                }
            }
            
            return true;
        });
    }
}