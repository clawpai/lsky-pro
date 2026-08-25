<?php

namespace App\Filament\Resources\SmsDriverResource\Pages;

use App\Filament\Resources\SmsDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsDriver extends EditRecord
{
    protected static string $resource = SmsDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
