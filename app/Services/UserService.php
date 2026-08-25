<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\User;
use App\Settings\UserSettings;
use App\UserCapacityFrom;
use App\UserGroupFrom;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    /**
     * 获取登录用户信息
     *
     * @return array
     */
    public function getProfile(): array
    {
        /** @var User $user */
        $user = Auth::user();

        $user->loadCount(['groups', 'capacities', 'orders', 'shares', 'tickets', 'photos', 'albums']);

        $user->append('avatar_url');

        $userInfo = $user->only([
            'id', 'avatar_url', 'name', 'username', 'email', 'phone', 'tagline', 'bio', 'url', 'location',
            'company', 'company_title', 'interests', 'socials', 'options', 'is_admin',
            'country_code', 'login_ip', 'email_verified_at', 'phone_verified_at', 'created_at',
        ]);

        return array_merge($userInfo, [
            'group_count' => $user->groups_count,
            'capacity_count' => $user->capacities_count,
            'order_count' => $user->orders_count,
            'share_count' => $user->shares_count,
            'ticket_count' => $user->tickets_count,
            'photo_count' => $user->photos_count,
            'album_count' => $user->albums_count,
            'used_storage' => round(floatval($user->photos()->sum('size')), 2),
            'total_storage' => \App\Facades\UserCapacityService::getUserTotalCapacity(),
        ]);
    }

    /**
     * 修改用户信息
     * @param array $data
     * @return bool
     */
    public function updateProfile(array $data): bool
    {
        /** @var User $user */
        $user = Auth::user();

        if (array_key_exists('avatar', $data) && $data['avatar'] instanceof UploadedFile) {
            $data['avatar'] = Storage::put('avatars', $data['avatar']);
        }

        return $user->fill($data)->save();
    }

    /**
     * 修改用户设置
     * @param array $data
     * @return bool
     */
    public function updateSetting(array $data): bool
    {
        /** @var User $user */
        $user = Auth::user();

        $user->options = array_merge($user->options->getArrayCopy(), $data);
        return $user->save();
    }

    /**
     * 绑定/换绑手机号
     *
     * @param string $phone 手机号
     * @param string $countryCode 国家代码
     * @return bool
     */
    public function bindPhone(string $phone, string $countryCode = 'cn'): bool
    {
        /** @var User $user */
        $user = Auth::user();

        $user->fill([
            'phone' => $phone,
            'country_code' => $countryCode,
            'phone_verified_at' => now(),
        ]);

        return $user->save();
    }

    /**
     * 绑定/换绑邮箱
     *
     * @param string $email 邮箱
     * @return bool
     */
    public function bindEmail(string $email): bool
    {
        /** @var User $user */
        $user = Auth::user();

        $user->fill([
            'email' => $email,
            'email_verified_at' => now(),
        ]);

        return $user->save();
    }

    /**
     * 修改指定用户密码
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function resetPassword(User $user, string $password): bool
    {
        $user->fill([
            'password' => Hash::make($password),
        ]);

        return $user->save();
    }

    /**
     * 创建新用户
     *
     * @param array $data
     * @return User
     */
    public function store(array $data): User
    {
        $data['options'] = array_merge([
            'language' => 'zh-CN',
            'show_original_photos' => false,
            'encode_copied_url' => true,
            'auto_upload_after_select' => false,
        ], $data['options'] ?? []);

        /** @var User $user */
        $user = User::create($data);

        // 用户默认组和容量
        $user->group()->create([
            'group_id' => Group::where('is_default', true)->value('id'),
            'from' => UserGroupFrom::System,
        ]);
        $user->capacity()->create([
            'capacity' => app(UserSettings::class)->initial_capacity,
            'from' => UserCapacityFrom::System,
        ]);

        return $user;
    }

    /**
     * 创建默认超级管理员
     *
     * @param array $data
     * @return User
     */
    public function createDefaultSuperAdmin(array $data): User
    {
        // 创建默认超级管理员
        /** @var User $user */
        $user = User::create(array_merge($data, [
            'is_admin' => true,
        ]));

        // 用户默认组和容量
        $user->group()->create([
            'group_id' => Group::first()->id,
            'from' => UserGroupFrom::System,
        ]);

        // 用户默认容量
        $user->capacity()->create([
            'capacity' => 1073741824, // 1t
            'from' => UserCapacityFrom::System,
        ]);

        return $user;
    }
}
