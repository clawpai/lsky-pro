<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Models\UserCapacity;
use App\Models\UserGroup;
use App\UserCapacityFrom;
use App\UserGroupFrom;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var User $user */
        $user = $this->record;

        /** @var UserGroup $userGroup */
        $userGroup = $user->groups()->with('group')->where('from', UserGroupFrom::System)->first();
        if ($userGroup && $userGroup->group) {
            $data['group_id'] = $userGroup->group->id;
        }

        /** @var UserCapacity $userCapacity */
        $userCapacity = $user->capacities()->where('from', UserCapacityFrom::System)->first();
        $data['capacity']= $userCapacity ? (float)$userCapacity->capacity : 0.00;

        return parent::mutateFormDataBeforeFill($data);
    }

    /**
     * @param User $record
     * @param array $data
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // 修改默认角色组
        $record->groups()->where('from', UserGroupFrom::System)->update(['group_id' => $data['group_id']]);

        // 用户默认容量
        $record->capacities()->where('from', UserCapacityFrom::System)->update(['capacity' => (float)$data['capacity']]);

        return parent::handleRecordUpdate($record, $data);
    }
}
