<?php

namespace App;

/**
 * 优惠券折扣类型枚举
 */
enum CouponType: string
{
    /** 直接抵扣 */
    case Direct = 'direct';

    /** 百分比抵扣 */
    case Percent = 'percent';
}
