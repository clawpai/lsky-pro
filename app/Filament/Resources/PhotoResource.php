<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages;
use App\Models\Photo;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.user');
    }

    public static function getModelLabel(): string
    {
        return __('admin/photo.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/photo.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'user', 'group', 'storage',
        ])->withCount('violations');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotos::route('/'),
            'view' => Pages\ViewPhoto::route('/{record}'),
        ];
    }
}
