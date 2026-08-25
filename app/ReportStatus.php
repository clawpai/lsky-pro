<?php

namespace App;

/**
 * 举报记录状态枚举
 */
enum ReportStatus: string
{
    /** 未处理 */
    case Unhandled = 'unhandled';

    /** 已处理 */
    case Handled = 'handled';
}
