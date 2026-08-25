<?php

namespace App\Http\Controllers\V2;

use App\Facades\PlanService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Support\R;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\HttpFoundation\Response;

class PlanController extends Controller
{
    /**
     * 套餐列表
     */
    public function index(QueryRequest $request): Response
    {
        $plans = PlanService::paginate($request->validated());

        $plans->getCollection()->each(fn(Plan $plan) => $plan->setVisible(['id', 'type', 'name', 'intro', 'features', 'badge']));

        return R::success(data: $plans);
    }

    /**
     * 套餐详情
     */
    public function show(string $id)
    {
        $plan = PlanService::show($id);

        $plan->load(['prices' => function (HasMany $builder) {
            $builder->orderBy('price');
        }]);

        $plan->prices->each(fn(PlanPrice $price) => $price->setVisible(['id', 'name', 'duration', 'price']));

        $plan->setVisible(['id', 'type', 'name', 'intro', 'features', 'badge', 'prices']);

        return R::success(data: $plan);
    }
}
