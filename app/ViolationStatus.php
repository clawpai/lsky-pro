<?php

namespace App;

/**
 * 违规记录状态枚举
 */
enum ViolationStatus: string
{
    /** 未处理 */
    case Unhandled = 'unhandled';

    /** 已处理 */
    case Handled = 'handled';
}
