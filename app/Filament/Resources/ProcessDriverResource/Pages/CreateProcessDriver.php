<?php

namespace App\Filament\Resources\ProcessDriverResource\Pages;

use App\DriverType;
use App\Filament\Resources\ProcessDriverResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProcessDriver extends CreateRecord
{
    protected static string $resource = ProcessDriverResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return parent::handleRecordCreation([...$data, ...['type' => DriverType::Process->value]]);
    }
}
