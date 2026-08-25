<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Scopes\FilterScope;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserTokenService
{
    /**
     * token 列表
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->tokens()->withGlobalScope('filter', new FilterScope(
            q: data_get($queries, 'q'),
            likes: ['name'],
            conditions: [
                'sort:last_used_at:ascend' => fn(Builder $builder) => $builder->orderBy('last_used_at'),
                'sort:last_used_at:descend' => fn(Builder $builder) => $builder->orderByDesc('last_used_at'),
                'sort:expires_at:ascend' => fn(Builder $builder) => $builder->orderBy('expires_at'),
                'sort:expires_at:descend' => fn(Builder $builder) => $builder->orderByDesc('expires_at'),
                'sort:created_at:ascend' => fn(Builder $builder) => $builder->orderBy('created_at'),
                'sort:created_at:descend' => fn(Builder $builder) => $builder->orderByDesc('created_at'),
            ]
        ))->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 创建 token
     */
    public function store(array $data): array
    {
        /** @var User $user */
        $user = Auth::user();

        $expiresAt = isset($data['expires_at'])
            ? Carbon::parse($data['expires_at'])
            : null;

        // 设置令牌能力（权限）
        $abilities = $data['abilities'] ?? ['*'];

        $token = $user->createToken(
            name: $data['name'],
            abilities: $abilities,
            expiresAt: $expiresAt,
        );

        return [
            'name' => $data['name'],
            'token' => $token->plainTextToken,
            'expires_at' => $expiresAt,
            'abilities' => $abilities,
        ];
    }

    /**
     * 删除 token
     */
    public function destroy(int $id): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->tokens()->where('id', $id)->delete() > 0;
    }
    
    /**
     * 获取当前授权用户的权限
     */
    public function getUserPermissions(): array
    {
        /** @var User $user */
        $user = Auth::user();
        
        // 获取当前请求使用的令牌
        $currentToken = $user->currentAccessToken();
        
        if (!$currentToken) {
            return [];
        }
        
        // 获取令牌的权限
        return [
            'token_name' => $currentToken->name,
            'abilities' => $currentToken->abilities,
            'last_used_at' => $currentToken->last_used_at,
            'expires_at' => $currentToken->expires_at,
        ];
    }
}