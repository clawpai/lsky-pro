<?php

namespace App\Http\Middleware;

use App\Settings\AppSettings;
use App\Support\R;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckExploreEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!app(AppSettings::class)->enable_explore) {
            return R::error('Gallery feature is disabled')->setStatusCode(404);
        }

        return $next($request);
    }
}
