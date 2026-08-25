<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Illuminate\Contracts\Support\Htmlable;

class Settings extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-m-cog';

    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin/setting.title');
    }
}
