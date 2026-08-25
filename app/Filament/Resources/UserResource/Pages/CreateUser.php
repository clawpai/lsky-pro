<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\UserCapacityFrom;
use App\UserGroupFrom;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var User $user */
        $user = parent::handleRecordCreation([...$data, ...[
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
            'phone_verified_at' => ! empty($data['phone']) ? now() : null,
        ]]);

        // 创建默认角色组
        $user->groups()->create([
            'from' => UserGroupFrom::System,
            'group_id' => $data['group_id'],
        ]);

        // 用户默认容量
        $user->capacities()->create([
            'from' => UserCapacityFrom::System,
            'capacity' => (float)$data['capacity'],
        ]);

        return $user;
    }
}
