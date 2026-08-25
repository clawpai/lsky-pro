<?php

namespace App;

/**
 * 社会化登录驱动枚举
 */
enum SocialiteProvider: string
{
    case Github = 'github';

    case QQ = 'qq';
}
