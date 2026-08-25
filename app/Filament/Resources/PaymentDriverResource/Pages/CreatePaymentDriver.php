<?php

namespace App\Filament\Resources\PaymentDriverResource\Pages;

use App\DriverType;
use App\Filament\Resources\PaymentDriverResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePaymentDriver extends CreateRecord
{
    protected static string $resource = PaymentDriverResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return parent::handleRecordCreation([...$data, ...['type' => DriverType::Payment->value]]);
    }
}
