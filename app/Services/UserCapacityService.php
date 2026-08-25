<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class UserCapacityService
{
    /**
     * 获取用户拥有的容量
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->capacities()->valid()->latest()->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 删除订阅
     */
    public function destroy(string $id): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->capacities()
                ->valid()
                ->where('id', $id)
                ->delete() > 0;
    }

    /**
     * 获取当前用户可用容量
     *
     * @return int|float
     */
    public function getUserTotalCapacity(): int|float
    {
        // 因为上传接口不需要登录，也调用了这个方法，所以需要使用 guard
        /** @var User $user */
        $user = Auth::guard('sanctum')->user();

        if (is_null($user)) {
            return 0.00;
        }

        return round((float)$user->capacities()->valid()->sum('capacity'), 2);
    }
}
