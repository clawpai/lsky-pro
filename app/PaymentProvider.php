<?php

namespace App;

/**
 * 支付驱动枚举
 */
enum PaymentProvider: string
{
    /** 支付宝 */
    case Alipay = 'alipay';

    /** 微信 */
    case Wechat = 'wechat';

    /** 银联 */
    case UniPay = 'unipay';

    /** PayPal */
    case Paypal = 'paypal';

    /** EPay */
    case EPay = 'epay';
}
