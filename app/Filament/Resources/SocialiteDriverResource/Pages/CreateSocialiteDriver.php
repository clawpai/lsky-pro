<?php

namespace App\Filament\Resources\SocialiteDriverResource\Pages;

use App\DriverType;
use App\Filament\Resources\SocialiteDriverResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSocialiteDriver extends CreateRecord
{
    protected static string $resource = SocialiteDriverResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return parent::handleRecordCreation([...$data, ...['type' => DriverType::Socialite->value]]);
    }
}
