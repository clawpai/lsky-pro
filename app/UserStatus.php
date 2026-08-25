<?php

namespace App;

enum UserStatus: string
{
    /** 正常 */
    case Normal = 'normal';

    /** 冻结 */
    case Frozen = 'frozen';
}
