<?php

namespace App;

/**
 * 支付方法枚举
 */
enum PaymentMethod: string
{
    /** @var string 网页支付 */
    case Web = 'web';

    /** @var string H5 支付 */
    case H5 = 'h5';

    /** @var string APP 支付 */
    case App = 'app';

    /** @var string 小程序 */
    case Mini = 'mini';

    /** @var string 刷卡支付 */
    case Pos = 'pos';

    /** @var string 扫码支付 */
    case Scan = 'scan';

    /** @var string 公众号支付 */
    case Mp = 'mp';
}
