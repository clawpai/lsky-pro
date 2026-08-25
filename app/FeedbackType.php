<?php

namespace App;

/**
 * 反馈类型枚举
 */
enum FeedbackType: string
{
    /** 一般意见 */
    case General = 'general';

    /** DMCA（数字千年版权）投诉 */
    case Dmca = 'dmca';
}
