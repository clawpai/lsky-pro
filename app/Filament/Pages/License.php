<?php

namespace App\Filament\Pages;

use App\Facades\AppService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class License extends Page
{
    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.pages.license';

    protected static bool $shouldRegisterNavigation = false;

    protected function getViewData(): array
    {
        return ['content' => Str::markdown(AppService::getAgreement())];
    }

    public function agree(): void
    {
        File::put(base_path('installed.lock'), 1);
        $this->redirect('/admin');
    }
}
