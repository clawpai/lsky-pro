<?php

namespace App\Filament\Resources\ProcessDriverResource\Pages;

use App\Filament\Resources\ProcessDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcessDriver extends EditRecord
{
    protected static string $resource = ProcessDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
