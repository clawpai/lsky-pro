<?php

namespace App\Filament\Resources\ScanDriverResource\Pages;

use App\Filament\Resources\ScanDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScanDriver extends EditRecord
{
    protected static string $resource = ScanDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
