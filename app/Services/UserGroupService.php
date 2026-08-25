<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class UserGroupService
{
    /**
     * 获取用户拥有的角色组
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->groups()
            ->valid()
            ->has('group')
            ->with('group')
            ->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 删除订阅
     */
    public function destroy(string $id): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->groups()
                ->valid()
                ->where('id', $id)
                ->delete() > 0;
    }
}
