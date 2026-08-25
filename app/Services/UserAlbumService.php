<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\Album;
use App\Models\Scopes\FilterScope;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserAlbumService
{
    /**
     * 获取用户相册分页列表
     *
     * @param array $queries
     * @return LengthAwarePaginator
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        return Auth::user()->albums()
            ->withGlobalScope('filter', new FilterScope(
                q: data_get($queries, 'q'),
                likes: ['name', 'intro'],
            ))
            ->withCount('photos as photo_count')
            ->with(['tags', 'photos' => fn(BelongsToMany $belongsToMany) => $belongsToMany->with('storage')->latest()->take(3)])
            ->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 新增相册
     */
    public function store(array $data): Album
    {
        if (Auth::user()->albums()->count() >= 9999) {
            throw new ServiceException('相册数量已达上限');
        }

        /** @var Album $album */
        $album = Auth::user()->albums()->create($data);
        return $album;
    }

    /**
     * 相册详情
     */
    public function show(string $id): Album
    {
        /** @var Album $album */
        $album = Auth::user()->albums()->withCount('photos as photo_count')->with('tags', 'photos.storage')->findOrFail($id);

        return $album;
    }

    /**
     * 修改相册
     */
    public function update(string $id, array $data): bool
    {
        /** @var Album $album */
        $album = Auth::user()->albums()->findOrFail($id);

        $album->fill($data);

        return $album->save();
    }

    /**
     * 删除相册
     */
    public function destroy(string $id): bool
    {
        return (bool)DB::transaction(function () use ($id) {
            /** @var User $user */
            $user = Auth::user();

            /** @var Album $album */
            $album = $user->albums()->findOrFail($id);

            // 删除所有关于该相册的分享
            $album->shares()->detach();

            return $album->delete();
        });
    }

    /**
     * 添加图片到相册
     *
     * @param string $id 相册ID
     * @param array $ids 图片ID
     * @return int
     */
    public function addPhotos(string $id, array $ids): int
    {
        /** @var Album $album */
        $album = Auth::user()->albums()->findOrFail($id);

        $photoIds = Auth::user()->photos()->whereIn('id', $ids)->pluck('id')->toArray();

        // 添加到相册
        $album->photos()->syncWithoutDetaching($photoIds);

        return count($photoIds);
    }

    /**
     * 从相册中移除图片
     *
     * @param string $id 相册ID
     * @param array $ids 图片ID
     * @return int
     */
    public function removePhotos(string $id, array $ids): int
    {
        /** @var Album $album */
        $album = Auth::user()->albums()->findOrFail($id);

        $photoIds = Auth::user()->photos()->whereIn('id', $ids)->pluck('id')->toArray();

        $album->photos()->detach($photoIds);

        return count($photoIds);
    }

    /**
     * 附加标签
     *
     * @param string $id 相册ID
     * @param array $tags 标签
     * @return int
     * @throws Throwable
     */
    public function attachTags(string $id, array $tags): int
    {
        /** @var Album $album */
        $album = Auth::user()->albums()->findOrFail($id);

        $attach = DB::transaction(function () use ($album, $tags) {
            $attach = [];
            foreach ($tags as $name) {
                $attach[Tag::firstOrCreate(compact('name'))->id] = ['user_id' => $album->user_id];
            }

            $album->tags()->sync($attach);

            return $attach;
        });

        return count($attach);
    }

    /**
     * 移除标签
     *
     * @param string $id 相册ID
     * @param array $tags 标签
     * @return int
     */
    public function removeTags(string $id, array $tags): int
    {
        /** @var Album $album */
        $album = Auth::user()->albums()->findOrFail($id);

        $ids = Tag::whereIn('name', $tags)->pluck('id')->toArray();

        $album->tags()->detach($ids);

        return count($ids);
    }
}