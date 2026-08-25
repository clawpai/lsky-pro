<?php

namespace App;

/**
 * 页面类型枚举
 */
enum PageType: string
{
    /** 内页 */
    case Internal = 'internal';

    /** 外链 */
    case External = 'external';
}
