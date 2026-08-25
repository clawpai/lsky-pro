<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\Group;
use ArrayObject;

class GroupService
{
    /**
     * 获取邮件驱动配置
     */
    public function getMailProviders(Group $group): array
    {
        $mail = $group->mailDrivers()->pluck('options');

        if ($mail->isEmpty()) {
            throw new ServiceException('未配置邮件服务，请联系管理员');
        }

        return $mail->map(fn(ArrayObject $options) => $options->getArrayCopy())->values()->toArray();
    }

    /**
     * 获取短信驱动配置
     */
    public function getSmsProviders(Group $group): array
    {
        $sms = $group->smsDrivers()->pluck('options');

        if ($sms->isEmpty()) {
            throw new ServiceException('未配置短信服务，请联系管理员');
        }

        return $sms->map(fn(ArrayObject $options) => $options->getArrayCopy())->values()->toArray();
    }
}
