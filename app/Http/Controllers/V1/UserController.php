<?php

namespace App\Http\Controllers\V1;

use App\Facades\UserCapacityService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->avatar) {
            $user->avatar = Storage::url($user->avatar);
        }

        $user->loadCount([
            'photos as image_num', 'albums as album_num',
        ]);

        $user->setVisible([
            'username', 'name', 'avatar', 'email', 'capacity', 'size', 'url', 'image_num', 'album_num', 'registered_ip'
        ]);

        $total = UserCapacityService::getUserTotalCapacity();
        $size = $user->photos()->sum('size');

        $data = array_merge($user->toArray(), [
            'registered_ip' => $user->register_ip,
            'url' => '',
            'capacity' => $total,
            'size' => $size,
        ]);

        return $this->success('success', $data);
    }
}
