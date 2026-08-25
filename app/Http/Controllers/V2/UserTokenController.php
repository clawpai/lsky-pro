<?php

namespace App\Http\Controllers\V2;

use App\Facades\UserTokenService;
use App\Http\Controllers\Controller;
use App\Http\Requests\QueryRequest;
use App\Http\Requests\TokenStoreRequest;
use App\Http\Resources\UserTokenResource;
use App\Support\R;
use Symfony\Component\HttpFoundation\Response;

class UserTokenController extends Controller
{
    public function index(QueryRequest $request): Response
    {
        $tokens = UserTokenService::paginate($request->validated());

        return R::success('success', UserTokenResource::collection($tokens)->response()->getData());
    }

    public function store(TokenStoreRequest $request): Response
    {
        $data = $request->validated();

        return R::success('success', UserTokenService::store($data));
    }

    public function destroy(int $id): Response
    {
        UserTokenService::destroy($id);

        return R::success('success');
    }
    
    /**
     * 获取当前授权用户拥有的权限
     */
    public function permissions(): Response
    {
        return R::success('success', UserTokenService::getUserPermissions());
    }
}
