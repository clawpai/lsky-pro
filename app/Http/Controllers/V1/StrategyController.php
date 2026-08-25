<?php

namespace App\Http\Controllers\V1;

use App\Models\Group;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class StrategyController extends BaseController
{
    public function index(Request $request): Response
    {
        /** @var Group $group */
        $group = Context::get('group');

        $strategies = $group->storages()->get()->each(fn(Storage $storage) => $storage->setVisible(['id', 'name']));
        return $this->success('success', compact('strategies'));
    }
}
