<?php

namespace App;

/**
 * 订单类型枚举
 */
enum OrderType: string
{
    /** 购买计划 */
    case Plan = 'plan';
}
