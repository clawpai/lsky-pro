<?php

namespace App;

enum UpgradeStatus: string
{
    /** 闲置 */
    case IDLE = 'idle';

    /** 检查中 */
    case CHECKING = 'checking';

    /** 有可用更新 */
    case AVAILABLE = 'available';

    /** 升级中 */
    case UPGRADING = 'upgrading';

    /** 升级完成 */
    case COMPLETED = 'completed';

    /** 无可用更新 */
    case UP_TO_DATE = 'up_to_date';

    /** 升级失败 */
    case FAILED = 'failed';
}
