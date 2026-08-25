<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\TicketStatus;
use Filament\Resources\Resource;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.operate');
    }

    public static function getModelLabel(): string
    {
        return __('admin/ticket.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/ticket.plural_model_label');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', TicketStatus::InProgress)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', TicketStatus::InProgress)->count() > 10 ? 'warning' : 'primary';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'view' => Pages\ViewTicket::route('/{record}'),
        ];
    }
}
