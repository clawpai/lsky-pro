<?php

namespace App\Filament\Resources\ScanDriverResource\Pages;

use App\DriverType;
use App\Filament\Resources\ScanDriverResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateScanDriver extends CreateRecord
{
    protected static string $resource = ScanDriverResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return parent::handleRecordCreation([...$data, ...['type' => DriverType::Scan->value]]);
    }
}
