<?php

namespace App;

/**
 * 图片内容安全服务检测结果状态
 */
enum ScanResultStatus: int
{
    /** 正常 */
    case Normal = 0;

    /** 疑似违规 */
    case Suspected = 1;

    /** 违规 */
    case Violation = 2;
}
