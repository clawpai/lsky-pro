<?php

namespace App;

/**
 * 用户容量来源枚举
 */
enum UserCapacityFrom: string
{
    /** 系统增加 */
    case System = 'system';

    /** 订阅增加 */
    case Subscribe = 'subscribe';
}
