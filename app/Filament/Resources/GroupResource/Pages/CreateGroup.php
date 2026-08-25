<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Models\Group;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;

    public function mount(): void
    {
        $this->form->fill(['options' => Group::getDefaultOptions()]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        // 如果选择了默认组或游客组，将其他角色组的默认组和游客组设置为 false
        if ($data['is_default'] ?? false) {
            Group::where('is_default', true)->update(['is_default' => false]);
        }

        if ($data['is_guest'] ?? false) {
            Group::where('is_guest', true)->update(['is_guest' => false]);
        }

        return parent::handleRecordCreation($data);
    }
}
