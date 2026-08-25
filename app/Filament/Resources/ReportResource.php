<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\ReportStatus;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 11;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.operate');
    }

    public static function getModelLabel(): string
    {
        return __('admin/report.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/report.plural_model_label');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', ReportStatus::Unhandled)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', ReportStatus::Unhandled)->count() > 10 ? 'warning' : 'primary';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'reportUser' => fn($query) => $query->withCount(['beReports' => fn($query) => $query->withTrashed()]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
        ];
    }
}
