<?php

namespace App;

/**
 * 工单等级枚举
 */
enum TicketLevel: string
{
    /** 低 */
    case Low = 'low';

    /** 中 */
    case Medium = 'medium';

    /** 高 */
    case High = 'high';
}
