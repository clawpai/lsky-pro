<?php

namespace App;

/**
 * 邮件驱动枚举
 */
enum MailProvider: string
{
    case Smtp = 'smtp';

    case Mailgun = 'mailgun';

    case Postmark = 'postmark';

    case Ses = 'ses';
}
