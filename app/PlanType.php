<?php

namespace App;

/**
 * 套餐枚举
 */
enum PlanType: string
{
    /** 会员 */
    case Vip = 'vip';

    /** 容量 */
    case Storage = 'storage';
}
