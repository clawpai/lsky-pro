<?php

namespace App\Filament\Resources\SmsDriverResource\Pages;

use App\DriverType;
use App\Filament\Resources\SmsDriverResource;
use App\VerifyCodeEvent;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSmsDriver extends CreateRecord
{
    protected static string $resource = SmsDriverResource::class;

    public function mount(): void
    {
        $this->form->fill([
            'options' => [
                'templates' => [
                    ['event' => VerifyCodeEvent::Register->value, 'template' => '', 'content' => ''],
                    ['event' => VerifyCodeEvent::Bind->value, 'template' => '', 'content' => ''],
                    ['event' => VerifyCodeEvent::ForgetPassword->value, 'template' => '', 'content' => ''],
                    ['event' => VerifyCodeEvent::Verify->value, 'template' => '', 'content' => ''],
                ]
            ]
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return parent::handleRecordCreation([...$data, ...['type' => DriverType::Sms->value]]);
    }
}
