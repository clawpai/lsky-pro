<?php

namespace App\Filament\Resources\SocialiteDriverResource\Pages;

use App\Filament\Resources\SocialiteDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocialiteDriver extends EditRecord
{
    protected static string $resource = SocialiteDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
