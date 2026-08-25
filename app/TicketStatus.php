<?php

namespace App;

/**
 * 工单状态枚举
 */
enum TicketStatus: string
{
    /** 进行中 */
    case InProgress = 'in_progress';

    /** 已完成 */
    case Completed = 'completed';
}
