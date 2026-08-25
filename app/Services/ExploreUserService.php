<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use App\ReportStatus;
use App\UserStatus;
use Illuminate\Pagination\LengthAwarePaginator;

class ExploreUserService
{
    /**
     * 获取用户信息
     */
    public function profile(string $username): array
    {
        $user = $this->getUser($username);

        $appends = [
            'photo_count' => $user->photos()->where('is_public', true)->count(),
            'album_count' => $user->albums()->where('is_public', true)->count(),
            'liked_photo_count' => $user->photos()->has('likes')->where('is_public', true)->count(),
            'liked_album_count' => $user->albums()->has('likes')->where('is_public', true)->count(),
        ];

        return [
            ...$user->append('avatar_url')->only([
                'id', 'avatar_url', 'name', 'username', 'location', 'bio', 'interests', 'socials', 'is_admin', 'created_at',
            ]),
            ...$appends,
        ];
    }

    /**
     * 获取指定用户的图片列表
     * @param string $username
     * @param array $queries
     * @return LengthAwarePaginator
     */
    public function photos(string $username, array $queries = []): LengthAwarePaginator
    {
        $user = $this->getUser($username);

        return $user->photos()->explore()->paginate($queries['per_page'] ?? 20);
    }

    /**
     * 获取指定用户的相册列表
     * @param string $username
     * @param array $queries
     * @return LengthAwarePaginator
     */
    public function albums(string $username, array $queries = []): LengthAwarePaginator
    {
        $user = $this->getUser($username);

        return $user->albums()->explore()->paginate($queries['per_page'] ?? 20);
    }

    /**
     * 举报用户
     */
    public function report(string $username, array $data): Report
    {
        $user = $this->getUser($username);

        /** @var Report $report */
        $report = $user->reports()->create([...$data, ...[
            'report_user_id' => $user->id,
            'status' => ReportStatus::Unhandled,
        ]]);

        return $report;
    }

    protected function getUser(string $username): User
    {
        return User::where('status', UserStatus::Normal)->where('username', $username)->firstOrFail();;
    }
}