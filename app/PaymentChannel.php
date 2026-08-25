<?php

namespace App;

/**
 * 支付渠道枚举
 */
enum PaymentChannel: string
{
    /** 支付宝 */
    case Alipay = 'alipay';

    /** 微信 */
    case Wechat = 'wechat';

    /** 银联 */
    case UniPay = 'unipay';

    /** PayPal */
    case Paypal = 'paypal';

    /** EPay - 微信支付 */
    case WXPay = 'wxpay';

    /** EPay - USDT-TRC20 */
    case USDT = 'usdt';

    /** EPay - QQ 钱包 */
    case QQPay = 'qqpay';

    /** EPay - 网银支付 */
    case Bank = 'bank';

    /** EPay - 京东支付 */
    case JDPay = 'jdpay';

    /** 统一支付 */
    case Unified = 'unified';
}
