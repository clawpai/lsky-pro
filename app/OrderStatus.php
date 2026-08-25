<?php

namespace App;

/**
 * 订单状态枚举
 */
enum OrderStatus: string
{
    /** 未支付 */
    case Unpaid = 'unpaid';

    /** 已支付 */
    case Paid = 'paid';

    /** 已取消 */
    case Cancelled = 'cancelled';
}
