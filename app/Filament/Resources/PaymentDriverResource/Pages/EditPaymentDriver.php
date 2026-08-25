<?php

namespace App\Filament\Resources\PaymentDriverResource\Pages;

use App\Filament\Resources\PaymentDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentDriver extends EditRecord
{
    protected static string $resource = PaymentDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
