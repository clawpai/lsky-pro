<?php

namespace App\Filament\Resources\HandleDriverResource\Pages;

use App\DriverType;
use App\Filament\Resources\HandleDriverResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateHandleDriver extends CreateRecord
{
    protected static string $resource = HandleDriverResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return parent::handleRecordCreation([...$data, ...['type' => DriverType::Handle->value]]);
    }
}
