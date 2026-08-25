<?php

namespace App\Providers;

use App\Exceptions\ApplicationNotInstalledException;
use App\Facades\AppService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * @throws ApplicationNotInstalledException
     */
    public function boot(): void
    {
        if (AppService::isInstalled()) {
            AppService::bootstrap();
        } else if (! app()->runningInConsole()) {
            throw new ApplicationNotInstalledException();
        }

        // 启用模型严格模式
        Model::shouldBeStrict();
    }
}
