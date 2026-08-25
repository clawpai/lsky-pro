<?php

namespace App;

/**
 * 用户组来源枚举
 */
enum UserGroupFrom: string
{
    /** 系统增加 */
    case System = 'system';

    /** 订阅增加 */
    case Subscribe = 'subscribe';
}
