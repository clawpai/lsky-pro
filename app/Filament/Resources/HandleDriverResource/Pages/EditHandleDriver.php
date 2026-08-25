<?php

namespace App\Filament\Resources\HandleDriverResource\Pages;

use App\Filament\Resources\HandleDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHandleDriver extends EditRecord
{
    protected static string $resource = HandleDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
