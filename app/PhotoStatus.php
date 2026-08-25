<?php

namespace App;

/**
 * 图片状态枚举
 */
enum PhotoStatus: string
{
    /** 审核中 */
    case Pending = 'pending';

    /** 正常 */
    case Normal = 'normal';

    /** 违规 */
    case Violation = 'violation';
}
