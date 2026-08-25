<?php

namespace App;

/**
 * 驱动类型枚举
 */
enum DriverType: string
{
    /** 审核驱动 */
    case Scan = 'scan';

    /** 短信驱动 */
    case Sms = 'sms';

    /** 邮件驱动 */
    case Mail = 'mail';

    /** 社会化登录驱动 */
    case Socialite = 'socialite';

    /** 支付驱动 */
    case Payment = 'payment';

    /** 照片处理驱动 */
    case Handle = 'handle';

    /** 云处理驱动 */
    case Process = 'process';
}
