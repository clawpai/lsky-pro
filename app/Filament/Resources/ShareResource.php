<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShareResource\Pages;
use App\Models\Share;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ShareResource extends Resource
{
    protected static ?string $model = Share::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.user');
    }

    public static function getModelLabel(): string
    {
        return __('admin/share.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/share.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShares::route('/'),
            'view' => Pages\ViewShare::route('/{record}'),
        ];
    }
}
