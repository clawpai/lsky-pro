<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AppInfo extends Widget
{
    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.app-info';
}
