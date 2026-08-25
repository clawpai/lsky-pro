<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PlanService
{
    /**
     * 获取已上架的套餐列表
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        return Plan::orderByDesc('sort')->where('is_up', true)->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 获取已上架的套餐详情
     */
    public function show(string $id): Plan
    {
        /** @var Plan $plan */
        $plan = Plan::where('is_up', true)->findOrFail($id);

        return $plan;
    }
}