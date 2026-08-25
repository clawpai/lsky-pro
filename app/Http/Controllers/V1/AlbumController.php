<?php

namespace App\Http\Controllers\V1;

use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AlbumController extends BaseController
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $albums = $user->albums()->withCount('photos as image_num')->where(function (Builder $builder) use ($request) {
            $builder->when($request->query('order') ?: 'newest', function (Builder $builder, $order) {
                match ($order) {
                    'earliest' => $builder->orderBy('id'),
                    'most' => $builder->orderByDesc('image_num'),
                    'least' => $builder->orderBy('image_num'),
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
                $builder->where('name', 'like', "%{$q}%")->orWhere('intro', 'like', "%{$q}%");
            });
        })->paginate(40)->withQueryString();
        $albums->getCollection()->each(function (Album $album) {
            $album->setVisible(['id', 'name', 'intro', 'image_num']);
        });

        return $this->success('success', $albums);
    }

    public function destroy(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $user->albums()->where('id', $request->route('id'))->delete();
        return $this->success('删除成功');
    }
}
